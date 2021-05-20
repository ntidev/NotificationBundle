<?php

namespace NTI\NotificationBundle\Controller;


use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use NTI\NotificationBundle\Entity\Destination;

/**
 * Class DestinationController
 * @package NTI\NotificationBundle\Controller
 * @Route("/destinations")
 */
class DestinationController extends Controller
{
    /**
     * @Route("/notifications", name="nti_notification_destinations_notifications", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     */
    public function getNotificationsAction(Request $request)
    {

        $loggedUser = $this->getUser();
        if (!$this->get('nti.notification.utilities.service')->isAuthenticated($loggedUser))
            return new JsonResponse("You are not authenticated. Please login to continue.",401);

        $notifications = $this->get('nti.notification.destination.service')->getUserNotifications($loggedUser);

        $context = SerializationContext::create()->setGroups(array('nti_notify_destination', 'nti_notify_destination_notification'));
        $notifications = json_decode($this->container->get('jms_serializer')->serialize($notifications, 'json', $context));
        return new JsonResponse($notifications);

    }


    /**
     * @Route("/{id}/status", name="nti_notification_destinations_status", options={"expose"=true})
     * @Method("PUT")
     * @param Request $request
     * @return JsonResponse
     */
    public function destinationStatusAction(Request $request, $id)
    {
        $loggedUser = $this->getUser();
        if (!$this->get('nti.notification.utilities.service')->isAuthenticated($loggedUser))
            return new JsonResponse("You are not authenticated. Please login to continue.",401);

        $destination = $this->getDoctrine()->getRepository(Destination::class)->find($id);
        if (!$destination)
            return new JsonResponse("Destination not found.", 404 );

        $data = json_decode($request->getContent(), true);
        if(!$data || !is_array($data))
            return new JsonResponse("Invalid data provided.", 400 );

        $result = $this->get('nti.notification.destination.service')->changeDestinationStatus($destination, $data);
        if ($result instanceof Destination){
            return new JsonResponse('Destination status changed', 200);
        }elseif ($result instanceof InvalidDestinationStatus){
            return new JsonResponse($result->getMessage(), 400);
        }
    }

}