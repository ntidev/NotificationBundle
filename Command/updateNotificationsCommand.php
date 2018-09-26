<?php

namespace NTI\NotificationBundle\Command;

use Doctrine\ORM\EntityManager;
use NTI\NotificationBundle\Entity\Application;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Entity\Status;
use NTI\NotificationBundle\Exception\NoDefaultApplicationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class updateNotificationsCommand extends ContainerAwareCommand
{
    /** @var  EntityManager $em */
    private $em;
    /** @var  ContainerInterface $container */
    private $container;
    /** @var  OutputInterface $output */
    private $output;

    protected function configure()
    {
        $this
            ->setName('nti:notifications:update')
            ->setDescription('Update notification state.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws NoDefaultApplicationException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # -- assigning global variable scope
        $this->output = $output;
        $this->container = $this->getContainer();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $stsExpired = $this->em->getRepository(Status::class)->findOneBy(array('code' => 'expired'));
        $stsAvailable = $this->em->getRepository(Status::class)->findOneBy(array('code' => 'active'));
        $dateNow = new \DateTime();

        $this->output->writeln('NTI:Notification:Update::: UPDATE STATE STARTED.');

        # -- validating the default application
        /** @var Application $default */
        $default = $this->em->getRepository(Application::class)->findOneBy(array('isDefault' => true, 'isActive' => true));
        if (!$default)
            throw new NoDefaultApplicationException("NTI:Notification:Update::: No Default Application Found.");

        # -- getting the notifications update state
        $notifications = $this->em->getRepository(Notification::class)->getStateUpdateNotifications();
        # -- changes states
        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            try {

                if($notification->getStatus()->getCode() == 'active'  && $notification->getExpirationDate() <= $dateNow){
                    $this->output->writeln('NTI:Notification:Update::: Processing ::: ' . $notification->getId());
                    $notification->setStatus($stsExpired);
                    $this->output->writeln('NTI:Notification:Update::: Changes ::: ' . $notification->getStatus()->getCode());
                    $this->output->writeln('NTI:Notification:Update::: Success ::: ' . $notification->getId());

                }elseif($notification->getStatus()->getCode() == 'scheduled'  && $notification->getScheduleDate() <= $dateNow) {
                    $this->output->writeln('NTI:Notification:Update::: Processing ::: ' . $notification->getId());
                    $notification->setStatus($stsAvailable);
                    $this->output->writeln('NTI:Notification:Update::: Changes ::: ' . $notification->getStatus()->getCode());
                    $this->output->writeln('NTI:Notification:Update::: Success ::: ' . $notification->getId());
                }

                $this->em->flush();
            } catch (\Exception $e) {
                $this->output->writeln('NTI:Notification:Update::: Database Error with ::: ' . $notification->getId());
                $this->output->writeln('NTI:Notification:Update::: Database Error with ::: ' . $e->getMessage());
            }
        }

        $this->output->writeln('NTI:Notification:Update::: UPDATE STATE FINISHED.');

    }


}
