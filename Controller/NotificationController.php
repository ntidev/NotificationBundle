<?php
namespace NTI\NotificationBundle\Controller;

use JMS\Serializer\SerializationContext;
use NTI\NotificationBundle\Entity\Application;
use NTI\NotificationBundle\Entity\Destination;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Exception\ApplicationNotFoundException;
use NTI\NotificationBundle\Exception\DataBaseDoctrineException;
use NTI\NotificationBundle\Exception\InvalidDestinationStatus;
use NTI\NotificationBundle\Exception\InvalidDestinationStructureException;
use NTI\NotificationBundle\Exception\NoDestinationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends Controller
{
    /**
     * FOR EXTERNAL APPLICATION USE 
     */
    
    /**
     * @Route("/{token}/notifications")
     * @Method("GET")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */
    public function getAllByApplication(Request $request, $token)
    {
        # -- some token validation can goes here.

        # -- get the application by token
        $application = $this->get('nti.notification.application.service')->getByToken($token);
        if (!$application)
            return new JsonResponse('Invalid Token.', 401);

        # -- capture pagination params here
        $notifications = $this->get('nti.notification.service')->getAllByApplication($application);

        # -- get the notifications by application

        $context = SerializationContext::create()->setGroups(array('nti_notify','nti_notify_app'));
        $notifications = json_decode($this->container->get('jms_serializer')->serialize($notifications, 'json', $context));
        return new JsonResponse($notifications);
    }

    /**
     * @Route("/{token}/notifications/{code}")
     * @Method("GET")
     * @param Request $request
     * @param $token
     * @param $code
     * @return JsonResponse
     */
    public function getOneByApplication(Request $request, $token, $code)
    {
        # -- getting the application by token
        $application = $this->get('nti.notification.application.service')->getByToken($token);
        if (!$application)
            return new JsonResponse('Invalid Token.', 401);

        # -- getting the notification
        $notification = $this->get('nti.notification.service')->getOneByCodeAndApplication($application,$code);
        if (!$notification)
            return new JsonResponse('Notification Not Found.', 404);

        $context = SerializationContext::create()->setGroups(array('nti_notify','nti_notify_app'));
        $notification = json_decode($this->container->get('jms_serializer')->serialize($notification, 'json', $context));
        return new JsonResponse($notification, 201);
    }

    /**
     * @Route("/{token}/notifications")
     * @Method("POST")
     * @param Request $request
     * @param $token
     * @return JsonResponse
     */
    public function postAction(Request $request, $token)
    {
        # -- some token validation can goes here

        $data = json_decode($request->getContent(), true);
        if(!$data)
            return new JsonResponse("Invalid data provided.", 400 );

        # -- get the application by token
        $application = $this->get('nti.notification.application.service')->getByToken($token);
        if (!$application)
            return new JsonResponse('Invalid Token.', 401);
        # -- checking access
        if (!$application->getWriteAccess())
            return new JsonResponse('Yo are not allow to create notifications.', 403);

        $notification = new Notification();
        $result = $this->get('nti.notification.service')->create($application,$data,$notification);
        if ($result instanceof Notification){
            $context = SerializationContext::create()->setGroups(array('nti_notify','nti_notify_app'));
            $notification = json_decode($this->container->get('jms_serializer')->serialize($result, 'json', $context));
            return new JsonResponse($notification, 201);
        }elseif ($result instanceof FormInterface){
            return new JsonResponse($this->get('nti.notification.utilities.service')->getFormErrors($result), 400);
        }elseif ($result instanceof InvalidDestinationStructureException){
            return new JsonResponse( 'Invalid destination format detected. Must be an array with a destinationId key.', 400);
        }elseif ($result instanceof ApplicationNotFoundException){
            return new JsonResponse( 'We can not find the requested application. Please check the information and try again.', 400);
        }elseif ($result instanceof DataBaseDoctrineException){
            return new JsonResponse( "Database Error: ".$result->getMessage(), 500);
        }

        return new JsonResponse( 'An unknown error occurred creating the notification.', 500);
    }

    /**
     * @Route("/{token}/notifications/{code}")
     * @Method("PUT")
     * @param Request $request
     * @param $token
     * @param $code
     * @return JsonResponse
     */
    public function putAction(Request $request, $token, $code)
    {
        # -- some token validation can goes here

        $data = json_decode($request->getContent(), true);
        if(!$data)
            return new JsonResponse("Invalid data provided.", 400 );

        # -- getting the application by token
        $application = $this->get('nti.notification.application.service')->getByToken($token);
        if (!$application)
            return new JsonResponse('Invalid Token.', 401);

        # -- getting the notification
        $notification = $this->get('nti.notification.service')->getOneByCodeAndApplication($application,$code);
        if (!$notification)
            return new JsonResponse('Notification Not Found.', 404);

        if ($notification->getStatus()->getCode() == 'cancelled')
            return new JsonResponse('Notification cancelled, no further operations allow.');

        if ($notification->getStatus()->getCode() == 'expired')
            return new JsonResponse('Notification expired, no further operations allow.');

        try {
            $result = $this->get('nti.notification.service')->update($application, $notification, $data);
            $context = SerializationContext::create()->setGroups(array('nti_notify','nti_notify_app'));
            $notification = json_decode($this->container->get('jms_serializer')->serialize($result, 'json', $context));
            return new JsonResponse($notification, 200);
        }catch (\Exception $exception) {
            if ($exception instanceof FormInterface) {
                return new JsonResponse($this->get('nti.notification.utilities.service')->getFormErrors($result), 400);
            } elseif ($exception instanceof InvalidDestinationStructureException) {
                return new JsonResponse('Invalid destination format detected. Must be an array with a destinationId key.', 400);
            } elseif ($exception instanceof NoDestinationException) {
                return new JsonResponse('The Notification must contain minimum one destination.', 400);
            }elseif ($exception instanceof ApplicationNotFoundException) {
                return new JsonResponse('The requested to application was not found.', 400);
            }elseif ($exception instanceof InvalidDestinationStatus) {
                return new JsonResponse($exception->getMessage(), 400);
            }
            return new JsonResponse( 'An unknown error occurred creating the notification.', 500);
        }
//        $result = $this->get('nti.notification.service')->update($application,$notification, $data);
//        if ($result instanceof Notification){
//            $context = SerializationContext::create()->setGroups(array('nti_notify','nti_notify_app'));
//            $notification = json_decode($this->container->get('jms_serializer')->serialize($result, 'json', $context));
//            return new JsonResponse($notification, 200);
//        }elseif ($result instanceof FormInterface) {
//            return new JsonResponse($this->get('nti.notification.utilities.service')->getFormErrors($result), 400);
//        }elseif ($result instanceof InvalidDestinationStructureException) {
//            return new JsonResponse('Invalid destination format detected. Must be an array with a destinationId key.', 400);
//        }elseif ($result instanceof NoDestinationException) {
//            return new JsonResponse('The Notification must contain minimum one destination.', 400);
//        }




        // -- old

//        }elseif ($result instanceof ApplicationNotFoundException){
//            return new JsonResponse( 'We can not find the requested application. Please check the information and try again.', 400);
//        }elseif ($result instanceof DataBaseDoctrineException){
//            return new JsonResponse( "Database Error: ".$result->getMessage(), 500);
//        }




    }

    /**
     * @Route("/{token}/notifications/{code}/destinations/{destinationId}")
     * @Method("PUT")
     * @param Request $request
     * @param $token
     * @param $code
     * @param $destinationId
     * @return JsonResponse
     */
    public function destinationStatus(Request $request, $token, $code, $destinationId)
    {
        $data = json_decode($request->getContent(), true);
        if(!$data || !is_array($data))
            return new JsonResponse("Invalid data provided.", 400 );

        # -- getting the application by token
        $application = $this->get('nti.notification.application.service')->getByToken($token);
        if (!$application)
            return new JsonResponse('Invalid Token.', 401);

        # -- getting the notification
        $notification = $this->get('nti.notification.service')->getOneByCodeAndApplication($application,$code);
        if (!$notification)
            return new JsonResponse('Notification Not Found.', 404);

        if ($notification->getStatus()->getCode() !== 'available')
            return new JsonResponse('Notification is not available.');

        # -- getting the destination
        $destination = $this->get('nti.notification.destination.service')->getOneByNotificationAndDestinationId($notification,$destinationId);
        if (!$destination)
            return new JsonResponse('Destination Not Found.', 404);

        $result = $this->get('nti.notification.destination.service')->changeDestinationStatus($destination, $data);
        if ($result instanceof Destination){
            $context = SerializationContext::create()->setGroups(array('nti_notify_destination'));
            $destination = json_decode($this->container->get('jms_serializer')->serialize($result, 'json', $context));
            return new JsonResponse($destination, 200);
        }elseif ($result instanceof InvalidDestinationStatus){
            return new JsonResponse($result->getMessage(), 400);
        }

        return new JsonResponse( 'An unknown error occurred changing the notification destination status.', 500);
    }

}