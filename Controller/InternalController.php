<?php
/**
 * Created by PhpStorm.
 * User: ealcantara
 * Date: 4/11/2018
 * Time: 11:38 AM
 */

namespace NTI\NotificationBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

class InternalController extends Controller
{
    /**
     * @Route("/notifications")
     * @param Request $request
     */
    public function notificationsByDestination(Request $request)
    {

        $user = $this->getUser();
//        $user->

    }

}