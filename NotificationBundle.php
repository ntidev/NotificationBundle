<?php

namespace NTI\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use NTI\NotificationBundle\DependencyInjection\NotificationExtension;

class NotificationBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new NotificationExtension();
        }
        return $this->extension;
    }
}
