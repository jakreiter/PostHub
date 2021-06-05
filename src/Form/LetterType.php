<?php

namespace App\Form;

use App\Entity\Letter;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Repository\LetterStatusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class LetterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('barcodeNumber')
            ->add('organization', EntityType::class, [
                'class' => 'App:Organization',
                'query_builder' => function (OrganizationRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC')
                        ->setMaxResults(5);
                },
                'choice_label' => function (Organization $organization) {
                    return $organization->getName().' '.($organization->getScan()?'📷':'x');
                },
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'placeholder' => '',
                'attr' => array(
                    'class' => 'organization-ajax-select'
                )
            ])
            ->add('status', EntityType::class, [
                'class' => 'App:LetterStatus',
                'query_builder' => function (LetterStatusRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.id', 'ASC')
                        ;
                },
                'multiple' => false,
                'expanded' => false,
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'label' => 'Scan (PDF file)',

                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    $data = $event->getData();
                    $submittedOrganizationId = $data['organization'];
                    if ($submittedOrganizationId) {
                        $form->add('organization', EntityType::class, [
                            'class' => 'App:Organization',
                            'query_builder' => function (OrganizationRepository $er) use ($submittedOrganizationId) {
                                return $er->createQueryBuilder('o')
                                    ->andWhere('o.id = :submittedOrganizationId')
                                    ->setParameter('submittedOrganizationId', $submittedOrganizationId);
                            },
                            'choice_label' => function (Organization $organization) {
                                return $organization->getName().' '.($organization->getScan()?'📷':'x');
                            },
                            'multiple' => false,
                            'expanded' => false,
                            'required' => true,
                            'placeholder' => '',
                            'attr' => array(
                                'class' => 'organization-ajax-select'
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
            'data_class' => Letter::class,
        ]);
    }
}
