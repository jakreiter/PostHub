<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('scan')
            ->add('location')
            ->add('owner', EntityType::class, [
                'class' => 'App:User',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC')
                        ->setMaxResults(5);
                },
                'choice_label' => function (User $user) {
                    return $user->getUsername().' ('.$user->getEmail().')';
                },
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'placeholder' => '',
                'attr' => array(
                    'class' => 'user-ajax-select'
                )
            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    $data = $event->getData();
                    $submittedUserId = $data['owner'];
                    if ($submittedUserId) {
                        $form->add('owner', EntityType::class, [
                            'class' => 'App:User',
                            'query_builder' => function (UserRepository $er) use ($submittedUserId) {
                                return $er->createQueryBuilder('o')
                                    ->andWhere('o.id = :submittedUserId')
                                    ->setParameter('submittedUserId', $submittedUserId);
                            },
                            'choice_label' => function (User $owner) {
                                return $owner->getUsername().' ('.$owner->getEmail().')';
                            },
                            'multiple' => false,
                            'expanded' => false,
                            'required' => true,
                            'placeholder' => '',
                            'attr' => array(
                                'class' => 'owner-ajax-select'
                            )
                        ]);
                    }

                }
            );
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
        ]);
    }
}
