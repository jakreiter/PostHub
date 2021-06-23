<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MeUserPassType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label'=>'Current password',
                'attr' => ['autocomplete'=>'current-password'],
            ])
            ->add('newPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field', 'autocomplete'=>'new-password']],
                'required' => true,
                'first_options' => ['label' => 'New password'],
                'second_options' => ['label' => 'Repeat New Password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password.',
                    ]),
                    new Length([
                        'min' => $_ENV['MIN_PASSWORD_LENGTH'],
                        //'minMessage' => 'Your password should be at least ' . $_ENV['MIN_PASSWORD_LENGTH'] . ' characters.',
                        'max' => 4096,
                    ]),
                ],
            ])
    
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'block_prefix'=>'chpa',
        ]);
    }
}
