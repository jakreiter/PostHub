<?php

namespace App\Form\Filter;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['required'=>false])
            ->add('firstName', TextType::class, ['required'=>false])
            ->add('lastName', TextType::class, ['required'=>false])
            ->add('email', EmailType::class, ['required'=>false])

        ;
    }

    public function getBlockPrefix()
    {
        return 'fi';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
