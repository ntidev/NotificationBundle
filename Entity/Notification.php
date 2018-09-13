<?php

namespace NTI\NotificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notification
 *
 * @ORM\Table(name="nti_notification")
 * @ORM\Entity(repositoryClass="NTI\NotificationBundle\Repository\NotificationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Notification
{
    CONST SYNC_STATUS_ERROR = 'error';
    CONST SYNC_STATUS_PENDING = 'pending';
    CONST SYNC_STATUS_SUCCESS = 'success';

    /**
     * @var int
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination_notification"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @ORM\Column(name="code", type="string", length=100, unique=true)
     */
    private $code;

    /**
     * @var string
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @ORM\Column(name="body", length=4294967295 )
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination_notification"})
     * @Serializer\SerializedName("createdAt")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination_notification"})
     * @Serializer\SerializedName("updatedAt")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @Serializer\SerializedName("scheduleDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @Assert\DateTime(format="m/d/Y h:i:s A" , message="invalid format.")
     * @ORM\Column(name="schedule_date", type="datetime")
     */
    private $scheduleDate;

    /**
     * @var \DateTime
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @Serializer\SerializedName("expirationDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     * @Assert\DateTime(format="m/d/Y h:i:s A" , message="invalid format.")
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    private $expirationDate;

    /**
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\Status")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     **/
    private $status;

    /**
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\Type")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     **/
    private $type;

    /**
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("fromApplication")
     *
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\Application")
     * @ORM\JoinColumn(name="from_application_id", referencedColumnName="id")
     **/
    private $fromApplication;

    /**
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("toApplication")
     *
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\Application")
     * @ORM\JoinColumn(name="to_application_id", referencedColumnName="id")
     **/
    private $toApplication;

    /**
     * @var bool
     *
     * @Serializer\Groups({"nti_notify","nti_notify_sync", "nti_notify_destination_notification"})
     * @Serializer\SerializedName("allDestinations")
     *
     * @ORM\Column(name="all_destination", type="boolean", options={"default": "0"},nullable=false)
     */
    private $allDestinations;

    /**
     * @var string
     *
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("syncStatus")
     *
     * @ORM\Column(name="sync_status", type="string", length=255)
     */
    private $syncStatus;

    /**
     * @var bool
     *
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("syncRemoteStatus")
     *
     * @ORM\Column(name="sync_remote_status", type="boolean", options={"default": "0"}, nullable=true)
     */
    private $syncRemoteStatus;

    /**
     * @var string
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("syncMessage")
     *
     * @ORM\Column(name="sync_message", type="text", nullable=true)
     */
    private $syncMessage;

    /**
     * @var \DateTime
     *
     * @Serializer\Groups("nti_notify")
     * @Serializer\SerializedName("syncDate")
     * @Serializer\Type("DateTime<'m/d/Y h:i:s A'>")
     *
     * @ORM\Column(name="sync_date", type="datetime", nullable=true)
     */
    private $syncDate;

    /**
     * @var ArrayCollection
     * @Serializer\Groups({"nti_notify","nti_notify_sync"})
     * @ORM\OneToMany(targetEntity="NTI\NotificationBundle\Entity\Destination", mappedBy="notification", cascade={"all"})
     */
    private $destinations;


    public function __construct()
    {
        $this->destinations = new ArrayCollection();
    }


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
     * @return Notification
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
     * Set subject
     *
     * @param string $subject
     *
     * @return Notification
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return Notification
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @ORM\PrePersist
     * @return Notification
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     * @ORM\PreUpdate
     * @ORM\PrePersist
     * @return Notification
     */
    public function setUpdatedAt()
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set scheduleDate
     *
     * @param \DateTime $scheduleDate
     *
     * @return Notification
     */
    public function setScheduleDate($scheduleDate)
    {
        $this->scheduleDate = $scheduleDate;

        return $this;
    }

    /**
     * Get scheduleDate
     *
     * @return \DateTime
     */
    public function getScheduleDate()
    {
        return $this->scheduleDate;
    }

    /**
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     *
     * @return Notification
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set status
     *
     * @param \NTI\NotificationBundle\Entity\Status $status
     *
     * @return Notification
     */
    public function setStatus(\NTI\NotificationBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NTI\NotificationBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type
     *
     * @param \NTI\NotificationBundle\Entity\Type $type
     *
     * @return Notification
     */
    public function setType(\NTI\NotificationBundle\Entity\Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \NTI\NotificationBundle\Entity\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set fromApplication
     *
     * @param \NTI\NotificationBundle\Entity\Application $fromApplication
     *
     * @return Notification
     */
    public function setFromApplication(\NTI\NotificationBundle\Entity\Application $fromApplication = null)
    {
        $this->fromApplication = $fromApplication;

        return $this;
    }

    /**
     * Get fromApplication
     *
     * @return \NTI\NotificationBundle\Entity\Application
     */
    public function getFromApplication()
    {
        return $this->fromApplication;
    }

    /**
     * Set toApplication
     *
     * @param \NTI\NotificationBundle\Entity\Application $toApplication
     *
     * @return Notification
     */
    public function setToApplication(\NTI\NotificationBundle\Entity\Application $toApplication = null)
    {
        $this->toApplication = $toApplication;

        return $this;
    }

    /**
     * Get toApplication
     *
     * @return \NTI\NotificationBundle\Entity\Application
     */
    public function getToApplication()
    {
        return $this->toApplication;
    }

    /**
     * Add destination
     *
     * @param \NTI\NotificationBundle\Entity\Destination $destination
     *
     * @return Notification
     */
    public function addDestination(\NTI\NotificationBundle\Entity\Destination $destination)
    {
        $this->destinations[] = $destination;

        return $this;
    }

    /**
     * Remove destination
     *
     * @param \NTI\NotificationBundle\Entity\Destination $destination
     */
    public function removeDestination(\NTI\NotificationBundle\Entity\Destination $destination)
    {
        $this->destinations->removeElement($destination);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * @param null $destinationId
     * @return bool
     */
    public function hasDestination($destinationId = null)
    {
        if (!$destinationId) return false;

        $found = false;

        /** @var Destination $destination */
        foreach ($this->destinations as $destination){
            if ($destination->getDestinationId() === $destinationId){
                $found = true;
                break;
            }
        }
        return $found;
    }

    /**
     * Set syncStatus
     *
     * @param string $syncStatus
     *
     * @return Notification
     */
    public function setSyncStatus($syncStatus)
    {
        $this->syncStatus = $syncStatus;

        return $this;
    }

    /**
     * Get syncStatus
     *
     * @return string
     */
    public function getSyncStatus()
    {
        return $this->syncStatus;
    }

    /**
     * Set syncMessage
     *
     * @param string $syncMessage
     *
     * @return Notification
     */
    public function setSyncMessage($syncMessage)
    {
        $this->syncMessage = $syncMessage;

        return $this;
    }

    /**
     * Get syncMessage
     *
     * @return string
     */
    public function getSyncMessage()
    {
        return $this->syncMessage;
    }

    /**
     * Set syncDate
     *
     * @param \DateTime $syncDate
     *
     * @return Notification
     */
    public function setSyncDate($syncDate)
    {
        $this->syncDate = $syncDate;

        return $this;
    }

    /**
     * Get syncDate
     *
     * @return \DateTime
     */
    public function getSyncDate()
    {
        return $this->syncDate;
    }

    /**
     * Set syncRemoteStatus
     *
     * @param boolean $syncRemoteStatus
     *
     * @return Notification
     */
    public function setSyncRemoteStatus($syncRemoteStatus)
    {
        $this->syncRemoteStatus = $syncRemoteStatus;

        return $this;
    }

    /**
     * Get syncRemoteStatus
     *
     * @return boolean
     */
    public function getSyncRemoteStatus()
    {
        return $this->syncRemoteStatus;
    }

    /**
     * Set allDestinations
     *
     * @param boolean $allDestinations
     *
     * @return Notification
     */
    public function setAllDestinations($allDestinations)
    {
        $this->allDestinations = $allDestinations;

        return $this;
    }

    /**
     * Get allDestinations
     *
     * @return boolean
     */
    public function getAllDestinations()
    {
        return $this->allDestinations;
    }
}
