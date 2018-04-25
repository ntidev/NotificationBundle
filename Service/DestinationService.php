<?php
/**
 * Created by PhpStorm.
 * User: ealcantara
 * Date: 3/15/2018
 * Time: 5:01 PM
 */

namespace NTI\NotificationBundle\Service;


use NTI\NotificationBundle\Entity\Destination;
use NTI\NotificationBundle\Entity\DestinationStatus;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Exception\DataBaseDoctrineException;
use NTI\NotificationBundle\Exception\InvalidDestinationStatus;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function getUserDestination(UserInterface $user)
    {
        try {
            $method = $this->destinationMethod;
            $destinationId = $user->$method();
            return $this->em->getRepository(Destination::class)->findOneBy(array('destinationId' => $destinationId));
        }catch (\Exception $e){
            return null;
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
     * @param UserInterface $user
     * @return array
     */
    public function getUserNotifications(UserInterface $user)
    {
        # -- getting the list here
        $destination = $this->getUserDestination($user);
        if ($destination instanceof Destination){
            return $this->em->getRepository(Destination::class)->getAvailableNotification($destination);
        }

        return array();

    }



}