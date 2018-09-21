<?php

namespace NTI\NotificationBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use JMS\Serializer\SerializationContext;
use NTI\NotificationBundle\Entity\Application;
use NTI\NotificationBundle\Entity\Destination;
use NTI\NotificationBundle\Entity\DestinationStatus;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Entity\Status;
use NTI\NotificationBundle\Entity\Type;
use NTI\NotificationBundle\Exception\ApplicationNotFoundException;
use NTI\NotificationBundle\Exception\DataBaseDoctrineException;
use NTI\NotificationBundle\Exception\InvalidApplicationRequestKeyException;
use NTI\NotificationBundle\Exception\InvalidDestinationStatus;
use NTI\NotificationBundle\Exception\InvalidDestinationStructureException;
use NTI\NotificationBundle\Exception\InvalidToApplicationException;
use NTI\NotificationBundle\Exception\NoCreateCanceledNotificationException;
use NTI\NotificationBundle\Exception\NoCreateExpiredNotificationException;
use NTI\NotificationBundle\Exception\NoDefaultApplicationException;
use NTI\NotificationBundle\Exception\NoDestinationException;
use NTI\NotificationBundle\Exception\SyncRequestException;
use NTI\NotificationBundle\Exception\ExpirationDateLowerThanScheduleDateException;
use NTI\NotificationBundle\Exception\ScheduleDateHigherThanExpirationDateException;
use NTI\NotificationBundle\Exception\ScheduleDateHigherToday;
use NTI\NotificationBundle\Form\NotificationType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotificationService
{

    private $container;
    private $em;
    private $appService;
    private $serializer;

    private $client;
    private $prefix;

    /**
     * NotificationService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->appService = $container->get('nti.notification.application.service');
        $this->prefix = $container->getParameter('nti.notification.route.prefix.parsed');
        $this->serializer = $container->get('jms_serializer');
        /** @var Client $client */
        $this->client = new Client();
    }

    /**
     * @param Application $application
     * @return mixed
     */
    public function getAllByApplication(Application $application)
    {
        return $this->em->getRepository(Notification::class)->findBy(array('fromApplication' => $application), array('scheduleDate' => 'asc'));
    }

    /**
     * @param Application $application
     * @param $code
     * @return null|Notification
     */
    public function getOneByCodeAndApplication(Application $application, $code)
    {
        return $this->em->getRepository(Notification::class)->findOneBy(array('fromApplication' => $application, 'code' => strtolower($code)));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getById(int $id)
    {
        return $this->em->getRepository(Notification::class)->find($id);
    }

    /**
     * @param Application $requestApp
     * @param $data
     * @param Notification $notification
     * @param string $formType
     * @return Notification|\Symfony\Component\Form\FormInterface
     * @throws ApplicationNotFoundException
     * @throws DataBaseDoctrineException
     * @throws InvalidDestinationStatus
     * @throws InvalidDestinationStructureException
     * @throws NoDestinationException
     * @throws ScheduleDateHigherThanExpirationDateException
     * @throws ExpirationDateLowerThanScheduleDateException
     * @throws NoCreateCanceledNotificationException
     * @throws NoCreateExpiredNotificationException
     */
    public function create(Application $requestApp, $data, Notification $notification, $formType = NotificationType::class)
    {
        # -- default application
        $default = $this->appService->getDefault();

        $notification->setFromApplication($requestApp);

        $form = $this->container->get('form.factory')->create($formType, $notification);
        $form->submit($data);
        if (!$form->isValid())
            return $form;

        # -- utils and dependencies
        $util = $this->container->get('nti.notification.utilities.service');
        $stsScheduled = $this->em->getRepository(Status::class)->findOneBy(array('code' => 'scheduled'));
        $stsAvailable = $this->em->getRepository(Status::class)->findOneBy(array('code' => 'available'));
        $stsExpired = $this->em->getRepository(Status::class)->findOneBy(array('code' => 'expired'));

        $stsDestinationUnread = $this->em->getRepository(DestinationStatus::class)->findOneBy(array('code' => 'unread'));

        if ($notification->getStatus() == $stsExpired)
            throw new NoCreateExpiredNotificationException();

        /**  -- initial validations -- */
        # -- Handle Notification Status
        if ($notification->getScheduleDate() != null && $notification->getScheduleDate() > new \DateTime()) {
            $notification->setStatus($stsScheduled);
        } elseif ($notification->getScheduleDate() == null) {
            $notification->setScheduleDate(new \DateTime());
            $notification->setStatus($stsAvailable);
        }

        if ($notification->getScheduleDate() > $notification->getExpirationDate())
            throw new ScheduleDateHigherThanExpirationDateException();
        if ($notification->getExpirationDate() < $notification->getScheduleDate())
            throw new ExpirationDateLowerThanScheduleDateException();

        $toApplication = $notification->getToApplication();

        /**  -- Handling External to Internal Notification --  */
        if ($requestApp !== $default) {

            $notification->setToApplication($default);
            $notification->setSyncStatus(Notification::SYNC_STATUS_SUCCESS);
            $notification->setSyncDate(new \DateTime());

        } elseif (null == $toApplication || !$toApplication instanceof Application) {
            /** -- application not found -- */

            throw new ApplicationNotFoundException();

        } elseif ($requestApp === $default && $default === $toApplication) {
            /**  -- Handling Internal Notification --  */

            $code = $util->getUUID();
            $notification->setCode($code);
            $notification->setSyncStatus(Notification::SYNC_STATUS_SUCCESS);
            $notification->setSyncDate(new \DateTime());

        } elseif ($requestApp === $default && $default !== $toApplication) {
            /**  -- Handling Internal to External Notification --  */

            $code = $util->getUUID();
            $notification->setCode($code);
            $notification->setSyncStatus(Notification::SYNC_STATUS_PENDING);
            $notification->setSyncDate(new \DateTime());
            $notification->setSyncRemoteStatus(false);
        }

        # -- handle destination
        $this->handleDestinations($notification, $data);
        try {
            $this->em->persist($notification);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new DataBaseDoctrineException($e->getMessage());
        }

        return $notification;

    }

    /**
     * @param Application $requestApp
     * @param Notification $notification
     * @param $data
     * @param bool $isPatch
     * @param string $formType
     * @return Notification|\Symfony\Component\Form\FormInterface
     * @throws ApplicationNotFoundException
     * @throws DataBaseDoctrineException
     * @throws InvalidDestinationStructureException
     * @throws NoDestinationException
     * @throws InvalidDestinationStatus
     * @throws ScheduleDateHigherThanExpirationDateException
     * @throws ExpirationDateLowerThanScheduleDateException
     */
    public function update(Application $requestApp, Notification $notification, $data, $isPatch = false, $formType = NotificationType::class)
    {

        $default = $this->appService->getDefault();
        # --
        $toApplication = $notification->getToApplication();
        $status = $notification->getStatus();
        $code = $notification->getCode();

        $form = $this->container->get('form.factory')->create($formType, $notification);
        $form->submit($data, !$isPatch);
        if (!$form->isValid())
            return $form;

        if ($notification->getScheduleDate() > $notification->getExpirationDate())
            throw new ScheduleDateHigherThanExpirationDateException();
        if ($notification->getExpirationDate() < $notification->getScheduleDate())
            throw new ExpirationDateLowerThanScheduleDateException();

        // -- can not change this
        $notification->setCode($code);
        $notification->setToApplication($toApplication);


        /**  -- Handling External to Internal Notification --  */
        if ($requestApp !== $default) {
            $notification->setSyncStatus(Notification::SYNC_STATUS_SUCCESS);
            $notification->setSyncDate(new \DateTime());
        } elseif (null == $notification->getToApplication() || !$notification->getToApplication() instanceof Application) {
            /** -- application not found -- */

            throw new ApplicationNotFoundException();

        } elseif ($requestApp === $default && $default === $toApplication) {
            /**  -- Handling Internal Notification --  */
            $notification->setSyncStatus(Notification::SYNC_STATUS_SUCCESS);
        } elseif ($requestApp === $default && $default !== $toApplication) {
            /**  -- Handling Internal to External Notification --  */
            $notification->setSyncStatus(Notification::SYNC_STATUS_PENDING);
            $notification->setSyncMessage(null); // --  clear old message
        }

        # -- handle destination
        $this->handleDestinations($notification, $data);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new DataBaseDoctrineException($e->getMessage());
        }

        return $notification;

    }

    /**
     * @param Notification $notification
     * @param $data
     * @return bool
     * @throws InvalidDestinationStatus
     * @throws InvalidDestinationStructureException
     * @throws NoDestinationException
     */
    private function handleDestinations(Notification $notification, $data)
    {
        $destinationService = $this->container->get('nti.notification.destination.service');
        $stsDestinationUnread = $this->em->getRepository(DestinationStatus::class)->findOneBy(array('code' => 'unread'));

        $current = $notification->getDestinations();
        $toDelete = array();

        # -- destinations
        $destinationData = (array_key_exists('destinations', $data) && is_array($data['destinations'])) ? $data['destinations'] : array();

        if (empty ($destinationData) || is_null($destinationData))
            throw new NoDestinationException();


        # -- adding new destinations.
        foreach ($destinationData as $newDestination) {
            if (is_array($newDestination) && array_key_exists('destinationId', $newDestination)) {
                # -- create destination if not exist.
                if ($notification->hasDestination($newDestination['destinationId']) === false) {
                    $destination = new Destination();
                    $destination->setStatus($stsDestinationUnread);
                    $destination->setDestinationId($newDestination['destinationId']);
                    $display = array_key_exists('destinationDisplay', $newDestination) ? $newDestination['destinationDisplay'] : $newDestination['destinationId'];
                    $destination->setDestinationDisplay($display);
                    $destination->setNotification($notification);
                    $notification->addDestination($destination);
                } else {
                    $destination = $destinationService->getOneByNotificationAndDestinationId($notification, $newDestination['destinationId']);
                    if ($destination && array_key_exists('status', $newDestination)) {
                        $validKeys = array('id', 'code');
                        $filter = array_filter($newDestination['status'], function ($key) use ($validKeys) {
                            return in_array($key, $validKeys);
                        }, ARRAY_FILTER_USE_KEY);
                        if (!$filter) {
                            throw new InvalidDestinationStatus("The provided data for the new destination is invalid.");
                        }

                        if ($destination->getStatus()->getCode() !== $newDestination['status']['code']) {
                            $status = $this->em->getRepository(DestinationStatus::class)->findOneBY($filter);
                            if ($status) {
                                $destination->setStatus($status);
                            }
                        }
                    }
                }
            } else {
                throw new InvalidDestinationStructureException();
            }
        }

        # -- removing unselected destinations
        /** @var Destination $removed */
        foreach ($current as $removed) {
            $found = false;
            foreach ($destinationData as $removedData) {
                if ($removedData['destinationId'] === $removed->getDestinationId()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $notification->removeDestination($removed);
                array_push($toDelete, $removed);
            }
        }

        # -- no destination check.
        if ($notification->getDestinations()->count() == 0)
            throw new NoDestinationException();

        # -- remove process
        foreach ($toDelete as $deleted) {
            $this->em->remove($deleted);
        }

        return true;
    }

    /**
     * This is main command function
     * @param Notification $notification
     * @param Application $default
     * @return Notification
     * @throws NoDefaultApplicationException
     * @throws InvalidToApplicationException
     * @throws InvalidApplicationRequestKeyException
     * @throws SyncRequestException
     */
    public function syncSendRemoteNotification(Notification $notification, Application $default)
    {
        if ($default->getIsDefault() == false)
            throw new NoDefaultApplicationException("Provided application is not the default application.");

        # -- check if is really an external notification
        $to = $notification->getToApplication();
        if ($to === null || $default === $to)
            throw new InvalidToApplicationException();

        # -- validating application request key
        if (!$to->getRequestKey())
            throw new InvalidApplicationRequestKeyException();

        # -- preparing the request
        $url = $to->getPath() . $this->prefix . $to->getRequestKey() . '/notifications';
        $method = $notification->getSyncRemoteStatus() === false ? 'POST' : 'PUT';

        if ($method == "PUT")
            $url = $url . "/" . $notification->getCode();

        $context = SerializationContext::create()->setGroups(array('nti_notify_sync'));
        $body = json_decode($this->container->get('jms_serializer')->serialize($notification, 'json', $context));

        # -- sending the request
        try {
            $this->client->request($method, $url, array(RequestOptions::JSON => $body));
            return $notification;
        } catch (\Exception $e) {
            throw new SyncRequestException($e->getMessage());
        }

    }

    /**
     * Return the list of active notification status
     * @return mixed
     */
    public function getActiveNotificationStatus()
    {
        return $this->em->getRepository(Status::class)->findBy(array('isActive' => true), array('name' => 'asc'));
    }

    /**
     * Return the list of active notification status
     * @return mixed
     */
    public function getActiveNotificationTypes()
    {
        return $this->em->getRepository(Type::class)->findBy(array('isActive' => true), array('name' => 'asc'));
    }


}
