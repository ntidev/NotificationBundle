<?php

namespace NTI\NotificationBundle\Service;


use NTI\NotificationBundle\Entity\Application;
use NTI\NotificationBundle\Form\ApplicationType;
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
     * @param $data
     * @param Application $application
     * @param string $formType
     */
    public function save($data, Application $application, $formType = ApplicationType::class)
    {

        $application = ($application) ? $application : new Application();

        $form = $this->container->get('form.factory')->create($formType, $application);
        $form->submit($data);

        if($form->isValid()) {
            $em = $this->container->get('doctrine')->getManager();
            if(!$application->getId()) {
                $em->persist($application);
            }
            try {
                $em->flush();
                return $application;
            } catch (\Exception $ex) {
                if($this->container->has('nti.logger')) {
                    $this->container->get('nti.logger')->logException($ex);
                }
                return false;
            }
        }
        return $form;
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

    /**
     * @param $token
     * @return null|Application
     */
    public function getByCode($code){
        if (!$code) return null;
        return $this->em->getRepository(Application::class)->findOneBy(array('code' => $code));
    }

}