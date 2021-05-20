<?php

namespace NTI\NotificationBundle\Service;


use NTI\NotificationBundle\Entity\Destination;
use NTI\NotificationBundle\Entity\DestinationStatus;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Exception\DataBaseDoctrineException;
use NTI\NotificationBundle\Exception\InvalidDestinationStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DestinationService
{
    private $container;
    private $em;

    private $destinationMethod;
    private $authRoles;
    private $grantedRoles;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->destinationMethod = $container->getParameter('nti.notification.user.destination.id.method');
        $this->authRoles = $container->getParameter('nti.notification.user.auth.roles');
        $this->grantedRoles = $container->getParameter('nti.notification.user.granted.roles');
    }

    public function getUserDestination($user)
    {
        try {
            $method = $this->destinationMethod;
            $destinationId = $user->$method();
            return $this->em->getRepository(Destination::class)->findOneBy(array('destinationId' => $destinationId));
        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * @param Notification $notification
     * @param $destinationId
     * @return null|Destination
     */
    public function getOneByNotificationAndDestinationId(Notification $notification, $destinationId)
    {
        $params = array(
            'notification' => $notification,
            'destinationId' => $destinationId
        );
        return $this->em->getRepository(Destination::class)->findOneBy($params);
    }

    /**
     * @param Destination $destination
     * @param $data
     * @return Destination|DataBaseDoctrineException|InvalidDestinationStatus
     */
    public function changeDestinationStatus(Destination $destination, $data){
        $validKeys = array('id','code');
        $filter = array_filter($data, function($key) use ($validKeys) { return in_array($key, $validKeys); }, ARRAY_FILTER_USE_KEY );
        if(!$filter) {
            return new InvalidDestinationStatus("The provided data for the new status is invalid.");
        }

        $status = $this->em->getRepository(DestinationStatus::class )->findOneBY($filter);
        if ($status) {
            $destination->setStatus($status);
            try{
                $this->em->flush();
            }catch (\Exception $e){
                return new DataBaseDoctrineException();
            }
        }

        return $destination;

    }

    /**
     * @param $user
     * @return array
     */
    public function getUserNotifications($user)
    {
        # -- getting the list here
        $destination = $this->getUserDestination($user);
        $this->includeToAllDestinationsNotifications($user, $destination);
        return $this->em->getRepository(Destination::class)->getAvailableNotification($destination);

    }

    /**
     * This methods is in charge of add the logged user as destination of the new notifications
     * marked with the allDestination property as true.
     *
     * @param Destination|null $destination
     */
    private function includeToAllDestinationsNotifications($user, Destination $destination = null)
    {
        $notifications = $this->em->getRepository(Notification::class)->getByAllDestinationActive($destination);
        $unread = $this->em->getRepository(DestinationStatus::class)->findOneBy(array('code'=>'unread'));

        $method = $this->destinationMethod;
        $destinationId = $user->$method();

        /** @var Notification $notification */
        foreach ($notifications as $notification){
            $d = new Destination();
            $d->setNotification($notification);
            $d->setDestinationId($destinationId);
            $d->setStatus($unread);
            $notification->addDestination($d);
            try{
                $this->em->persist($d);
                $this->em->flush();
            }catch (\Exception $e){
                throw new DataBaseDoctrineException('Error creating the destination for new notifications target to all destinations.');
            }
        }


    }


}