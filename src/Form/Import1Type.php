<?php

namespace App\Form;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class Import1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registered', ChoiceType::class, [
                'choices'  => [
                    '' => null,
                    'aaaa' => 11,
                    'bbb' => 33,
                ],
                'label' => 'Company from source system'
            ])

            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'query_builder' => function (OrganizationRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC')
                        ->setMaxResults(5);
                },
                'choice_label' => function (Organization $organization) {
                    return $organization->getName() . ' ' . ($organization->getScan() ? '📷' : '🔒');
                },
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'placeholder' => '',
                'attr' => [
                    'class' => 'organization-ajax-filter-select'
                ]
            ])

            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    /** @var @var Letter $letter */
                    $letter = $event->getData();
                    if ($letter && $letter->getOrganization()) {
                        $submittedOrganizationId = $letter->getOrganization()->getId();
                        $form->add('organization', EntityType::class, [
                            'class' => Organization::class,
                            'query_builder' => function (OrganizationRepository $er) use ($submittedOrganizationId) {
                                return $er->createQueryBuilder('o')
                                    ->andWhere('o.id = :submittedOrganizationId')
                                    ->setParameter('submittedOrganizationId', $submittedOrganizationId);
                            },
                            'choice_label' => function (Organization $organization) {
                                return $organization->getName() . ' ' . ($organization->getScan() ? '📷' : '🔒');
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
                    $submittedOrganizationId = $data['organization'];
                    if ($submittedOrganizationId) {
                        $form->add('organization', EntityType::class, [
                            'class' => Organization::class,
                            'query_builder' => function (OrganizationRepository $er) use ($submittedOrganizationId) {
                                return $er->createQueryBuilder('o')
                                    ->andWhere('o.id = :submittedOrganizationId')
                                    ->setParameter('submittedOrganizationId', $submittedOrganizationId);
                            },
                            'choice_label' => function (Organization $organization) {
                                return $organization->getName() . ' ' . ($organization->getScan() ? '📷' : '🔒');
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

                    $registeredId = $data['registered'];
                    if ($registeredId) {
                        $form->add('registered', ChoiceType::class, [
                            'choices'  => [
                                "old org $registeredId" => $registeredId,
                            ],
                        ]);
                    }

                }
            );

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
