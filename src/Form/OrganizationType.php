<?php

namespace App\Form;

use App\Entity\Organization;
use App\Entity\User;
use App\Entity\ScanPlan;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('allowScanDownloadWithoutLogin')
            ->add('commaSeparatedEmails', TextType::class, ['required'=>false])
            ->add('scanPlan', EntityType::class, [
                'class' => ScanPlan::class,
                'required' => true,
                'choice_translation_domain' => null,
                'choice_label' => "name"
                ])
            ->add('location')
            ->add('owner', EntityType::class, [
                'class' => 'App:User',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.email', 'ASC')
                        ->setMaxResults(5);
                },
                'choice_label' => function (User $user) {
                    return $user->getEmail();
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
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    /** @var Organization $organization */
                    $organization = $event->getData();
                    if ($organization->getOwner()) {
                        $loadedUserId = $organization->getOwner()->getId();
                        $form->add('owner', EntityType::class, [
                            'class' => 'App:User',
                            'query_builder' => function (UserRepository $er) use ($loadedUserId) {
                                return $er->createQueryBuilder('o')
                                    ->andWhere('o.id = :loadedUserId')
                                    ->setParameter('loadedUserId', $loadedUserId);
                            },
                            'choice_label' => function (User $owner) {
                                return $owner->getEmail();
                            },
                            'multiple' => false,
                            'expanded' => false,
                            'required' => true,
                            'placeholder' => '',
                            'attr' => array(
                                'class' => 'organization-ajax-filter-select'
                            )
                        ]);
                    }

                }
            )
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
                                return $owner->getEmail();
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
