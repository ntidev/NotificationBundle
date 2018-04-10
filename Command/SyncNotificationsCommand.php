<?php

namespace NTI\NotificationBundle\Command;

use Doctrine\ORM\EntityManager;
use NTI\NotificationBundle\Entity\Application;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Exception\NoDefaultApplicationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SyncNotificationsCommand extends ContainerAwareCommand
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
            ->setName('nti:notifications:sync')
            ->setDescription('Send the external notifications to their respective applications.')
        ;
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
        $notificationSrv = $this->container->get('nti.notification.service');

        $this->output->writeln('NTI:Notification:Sync::: SYNCHRONIZATION STARTED.');

        # -- validating the default application
        /** @var Application $default */
        $default = $this->em->getRepository(Application::class)->findOneBy(array('isDefault'=>true, 'isActive'=>true));
        if (!$default)
            throw new NoDefaultApplicationException("NTI:Notification:Sync::: No Default Application Found.");

        # -- getting the notifications
        $notifications = $this->em->getRepository(Notification::class)->getExternalNotifications();

        # -- send them to sync here
        /** @var Notification $notification */
        foreach ($notifications as $notification){
            try{

                $notificationSrv->syncSendRemoteNotification($notification,$default);

                # -- more success response validation can be added here. ** NOT DOING IT FOR NOW **
                $notification->setSyncRemoteStatus(true);
                $notification->setSyncMessage(Notification::SYNC_STATUS_SUCCESS);
                $notification->setSyncDate(new \DateTime());

            }catch (\Exception $e){

                $notification->setSyncMessage(Notification::SYNC_STATUS_ERROR);
                $notification->setSyncMessage($e->getMessage());
                $notification->setSyncDate(new \DateTime());

            }

            try{
                $this->em->flush();
            }catch (\Exception $e){
                /**
                 * here it is necessary to find the way of let now the process that check for the existence of the notification
                 * for post actions (syncRemoteStatus false), in order to prevent duplicated constraint error from the
                 * remote application.
                 */

            }

        }

        $this->output->writeln('NTI:Notification:Sync::: SYNCHRONIZATION FINISHED.');

    }



}