<?php

namespace NTI\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Destination
 *
 * @ORM\Table(name="nti_notification_destination")
 * @ORM\Entity(repositoryClass="NTI\NotificationBundle\Repository\DestinationRepository")
 */
class Destination
{
    /**
     * @var int
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination"})
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination","nti_notify_sync"})
     * @Serializer\SerializedName("destinationId")
     *
     * @ORM\Column(name="destination_id", type="string", length=100)
     */
    private $destinationId;

    /**
     * @var string
     *
     * @Serializer\Groups({"nti_notify","nti_notify_destination","nti_notify_sync"})
     * @Serializer\SerializedName("destinationDisplay")
     *
     * @ORM\Column(name="destination_display", type="string", length=255, nullable=true)
     */
    private $destinationDisplay;

    /**
     * @Serializer\Groups({"nti_notify_destination_notification"})
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\Notification",inversedBy="destinations")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $notification;

    /**
     * @Serializer\Groups({"nti_notify","nti_notify_destination"})
     *
     * @ORM\ManyToOne(targetEntity="NTI\NotificationBundle\Entity\DestinationStatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     **/
    private $status;


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
     * Set destinationId
     *
     * @param string $destinationId
     *
     * @return Destination
     */
    public function setDestinationId($destinationId)
    {
        $this->destinationId = $destinationId;

        return $this;
    }

    /**
     * Get destinationId
     *
     * @return string
     */
    public function getDestinationId()
    {
        return $this->destinationId;
    }

    /**
     * Set destinationDisplay
     *
     * @param string $destinationDisplay
     *
     * @return Destination
     */
    public function setDestinationDisplay($destinationDisplay)
    {
        $this->destinationDisplay = $destinationDisplay;

        return $this;
    }

    /**
     * Get destinationDisplay
     *
     * @return string
     */
    public function getDestinationDisplay()
    {
        return $this->destinationDisplay;
    }

    /**
     * Set notification
     *
     * @param \NTI\NotificationBundle\Entity\Notification $notification
     *
     * @return Destination
     */
    public function setNotification(\NTI\NotificationBundle\Entity\Notification $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \NTI\NotificationBundle\Entity\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set status
     *
     * @param \NTI\NotificationBundle\Entity\DestinationStatus $status
     *
     * @return Destination
     */
    public function setStatus(\NTI\NotificationBundle\Entity\DestinationStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \NTI\NotificationBundle\Entity\DestinationStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
