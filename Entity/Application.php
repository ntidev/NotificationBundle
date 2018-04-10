<?php

namespace NTI\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Application
 *
 * @ORM\Table(name="nti_notification_application")
 * @ORM\Entity(repositoryClass="NTI\NotificationBundle\Repository\ApplicationRepository")
 */
class Application
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups("nti_notify_app")
     *
     * @ORM\Column(name="code", type="string", length=60, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @Serializer\Groups("nti_notify_app")
     *
     * @ORM\Column(name="Name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("isActive")
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     * @Serializer\Groups("nti_notify_app")
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("isDefault")
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $isDefault;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("readAccess")
     *
     * @ORM\Column(name="read_access", type="boolean")
     */
    private $readAccess;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("writeAccess")
     *
     * @ORM\Column(name="write_access", type="boolean")
     */
    private $writeAccess;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="request_key", type="string", length=255, unique=true, nullable=true)
     */
    private $requestKey;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("isUp")
     *
     * @ORM\Column(name="is_up", type="boolean")
     */
    private $isUp;

    /**
     * @var string
     *
     * @Serializer\Groups("nti_notify_app")
     * @Serializer\SerializedName("errorMessage")
     *
     * @ORM\Column(name="error_message", type="string", length=255, nullable=true)
     */
    private $errorMessage;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Application
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Application
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Application
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Application
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set isDefault
     *
     * @param boolean $isDefault
     *
     * @return Application
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set readAccess
     *
     * @param boolean $readAccess
     *
     * @return Application
     */
    public function setReadAccess($readAccess)
    {
        $this->readAccess = $readAccess;

        return $this;
    }

    /**
     * Get readAccess
     *
     * @return bool
     */
    public function getReadAccess()
    {
        return $this->readAccess;
    }

    /**
     * Set writeAccess
     *
     * @param boolean $writeAccess
     *
     * @return Application
     */
    public function setWriteAccess($writeAccess)
    {
        $this->writeAccess = $writeAccess;

        return $this;
    }

    /**
     * Get writeAccess
     *
     * @return bool
     */
    public function getWriteAccess()
    {
        return $this->writeAccess;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return Application
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set isUp
     *
     * @param boolean $isUp
     *
     * @return Application
     */
    public function setIsUp($isUp)
    {
        $this->isUp = $isUp;

        return $this;
    }

    /**
     * Get isUp
     *
     * @return bool
     */
    public function getIsUp()
    {
        return $this->isUp;
    }

    /**
     * Set errorMessage
     *
     * @param string $errorMessage
     *
     * @return Application
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * Get errorMessage
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Set requestKey
     *
     * @param string $requestKey
     *
     * @return Application
     */
    public function setRequestKey($requestKey)
    {
        $this->requestKey = $requestKey;

        return $this;
    }

    /**
     * Get requestKey
     *
     * @return string
     */
    public function getRequestKey()
    {
        return $this->requestKey;
    }
}
