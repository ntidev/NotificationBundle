<?php

namespace NTI\NotificationBundle\Service;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UtilitiesService
{
    private $container;
    private $authRoles;
    private $grantedRoles;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->authRoles = $container->getParameter('nti.notification.user.auth.roles');
        $this->grantedRoles = $container->getParameter('nti.notification.user.granted.roles');
    }

    /**
     * Generates and return an UUID V4 String
     * @return string
     */
    public static function getUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }


    public function getFormErrors(Form $form) {
         $errors = [];

         foreach ($form->all() as $child) {
             $errors = array_merge(
                 $errors,
                 $this->getFormErrors($child)
             );
         }

         foreach ($form->getErrors() as $error) {
                $path = $error->getCause()->getPropertyPath();
                $path = preg_replace("/^(data.)|(.data)|(\\])|(\\[)|children/", '', $path);
                $errors[$path] = $error->getMessage();
         }

         return $errors;

    }

    /**
     * @param $user
     * @return bool
     */
    public function isAuthenticated($user = null){
        $authenticated = false;

        if (!is_array($this->authRoles) && !$user->hasRole($this->authRoles))
            return $authenticated;

        if (is_array($this->authRoles)) {
            foreach ($this->authRoles as $role) {
                if ($user->hasRole($role)) {
                    $authenticated = true;
                    break;
                }
            }
        }
        

        return $authenticated;

    }

}
