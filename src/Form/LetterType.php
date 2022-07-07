<?php

namespace App\Form;

use App\Entity\Letter;
use App\Entity\Organization;
use App\Entity\LetterStatus;
use App\Repository\OrganizationRepository;
use App\Repository\LetterStatusRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'ltr_title' // form-control
                ]
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
                    'class' => 'ltr_organization organization-ajax-select'
                ]
            ])
            ->add('status', EntityType::class, [
                'class' => LetterStatus::class,
                'query_builder' => function (LetterStatusRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.id', 'ASC');
                },
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'choice_translation_domain' => true
            ])
            ->add('file', FileType::class, [
                'label' => 'Scan (PDF file)',

                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => $_ENV['MAX_UPLOAD_FILE_SIZE'],
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $form = $event->getForm();
                    /** @var @var Letter $letter */
                    $letter = $event->getData();
                    if ($letter->getOrganization()) {
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
                                'class' => 'ltr_organization organization-ajax-filter-select'
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
                                'class' => 'ltr_organization organization-ajax-filter-select'
                            )
                        ]);
                    }

                }
            );
            if ($_ENV['USE_BARCODES']) {
                $builder->add('barcodeNumber');
            }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Letter::class,
        ]);
    }
}
