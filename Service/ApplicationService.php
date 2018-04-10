<?php
/**
 * Created by PhpStorm.
 * User: ealcantara
 * Date: 3/5/2018
 * Time: 2:53 PM
 */

namespace NTI\NotificationBundle\Service;


use NTI\NotificationBundle\Entity\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ApplicationService
{
    private $container;
    private $em;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * @return null|Application
     */
    public function getDefault(){
        return $this->em->getRepository(Application::class)->findOneBy(array('isDefault' => true));
    }

    /**
     * @param $token
     * @return null|Application
     */
    public function getByToken($token){
        if (!$token) return null;
        return $this->em->getRepository(Application::class)->findOneBy(array('token' => $token));
    }

}