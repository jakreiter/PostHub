<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $allRoles = [];
        foreach (User::ALL_ROLES as $oneRole) {
            $allRoles[$oneRole] = $oneRole;
        }


        $builder
            ->add('username')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('enabled')
            ->add('location')
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'choices' => $allRoles,
                'choice_attr' => [
                    'ROLE_SUPER_ADMIN' => ['disabled' => 'disabled'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
