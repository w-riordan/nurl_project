<?php

namespace AppBundle\Form;

use AppBundle\Entity\Collection;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NurlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em =$options['entity_manager'];
        $user= $options['user'];
        $collections= $em->getRepository("AppBundle:Collection")->findByOwner($user);
        $builder->add('title')->add('uRL')->add('notes');
        if ($options['show_public']){
            $builder->add('public');
        }
        $builder->add('tag', EntityType::class,array(
            'class' => 'AppBundle:Tag',
            'required' => true,
            'placeholder' => 'Choose A Tag'
        ))
            ->add('collection',EntityType::class,array(
            'class' => 'AppBundle:Collection',
            'choices' => $collections,
            'placeholder' => 'None',
            'empty_data'  => null,
            'required' => false
        ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('entity_manager');
        $resolver->setRequired('user');
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Nurl',
            'show_public' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_nurl';
    }


}
