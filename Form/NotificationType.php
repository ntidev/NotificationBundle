<?php

namespace NTI\NotificationBundle\Form;

use Doctrine\ORM\EntityManager;
use NTI\NotificationBundle\Entity\Notification;
use NTI\NotificationBundle\Form\DataTransformers\ApplicationTransformer;
use NTI\NotificationBundle\Form\DataTransformers\StatusTransformer;
use NTI\NotificationBundle\Form\DataTransformers\TypeTransformer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationType extends AbstractType
{
    private $em;
    private $container;

    public function __construct(ContainerInterface $container,EntityManager $em)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('subject')
            ->add('body')
            ->add('allDestinations')
//            ->add('scheduleDate','datetime', array('widget' => 'single_text', 'date_format' => 'm/d/Y h:i A'))
            ->add('scheduleDate',TextType::class ,array("invalid_message" => "Invalid Effective schedule date  provided. (i.e. 01/01/2099 00:00:00 AM)"))
//            ->add('expirationDate','datetime', array('widget' => 'single_text', 'date_format' => 'm/d/Y h:i A'))
            ->add('expirationDate',TextType::class,array("invalid_message" => "Invalid Effective expiration date  provided. (i.e. 01/01/2099 00:00:00 AM)"))
            ->add('status', TextType::class )
            ->add('type', TextType::class)
//            ->add('fromApplication', TextType::class)
            ->add('toApplication', TextType::class);

        # -- data transformers
        $builder->get('status')->addModelTransformer(new StatusTransformer($this->em));
        $builder->get('type')->addModelTransformer(new TypeTransformer($this->em));
//        $builder->get('fromApplication')->addModelTransformer(new ApplicationTransformer($this->em));
        $builder->get('toApplication')->addModelTransformer(new ApplicationTransformer($this->em));

        $builder->get('scheduleDate')->addModelTransformer(new DateTimeToStringTransformer(null, null, 'm/d/Y h:i A'));
        $builder->get('expirationDate')->addModelTransformer(new DateTimeToStringTransformer(null, null, 'm/d/Y h:i A'));

    }

//    public function onSubmitData(FormEvent $event) {
//
//        /** @var Notification $notification */
//        $notification = $event->getData();
//        # -- setting notification code
//        if(null == $notification->getId()) {
//            $code = $this->container->get('nti.notification.utilities.service')->getUUID();
//            $notification->setCode($code);
//        }
//    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NTI\NotificationBundle\Entity\Notification',
            'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nti_notificationbundle_notification';
    }


}
