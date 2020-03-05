<?php

namespace NTI\NotificationBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApplicationType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', TextType::class )
            ->add('name', TextType::class )
            ->add('isActive')
            ->add('path', TextType::class )
            ->add('isDefault')
            ->add('readAccess')
            ->add('writeAccess')
            ->add('token', TextType::class )
            ->add('requestKey', TextType::class )
            ->add('isUp')
            ->add('errorMessage', TextType::class );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NTI\NotificationBundle\Entity\Application',
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
