<?php

namespace App\Form\Filter;

use App\Entity\Organization;
use App\Entity\LetterStatus;
use App\Repository\OrganizationRepository;
use App\Repository\LetterStatusRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class LetterFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['required' => false])
            ->add('organization', EntityType::class, [
                'class' => 'App:Organization',
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
                'required' => false,
                'placeholder' => '',
                'attr' => array(
                    'class' => 'organization-ajax-filter-select'
                )
            ])
            ->add('status', EntityType::class, [
                'class' => 'App:LetterStatus',
                'query_builder' => function (LetterStatusRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC');
                },
                'choice_label' => function (LetterStatus $letterStatus) {
                    return $letterStatus->getName();
                },
                'choice_translation_domain' => true,
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => '',
                'attr' => array()
            ])
            ->add('hasScanOrdered', ChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => ['No' => -1, 'Yes' => 1],

            ])
            ->add('hasOrderedScanInserted', ChoiceType::class, [
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => ['No' => -1, 'Yes' => 1],

            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    $data = $event->getData();
                    if (isset($data['organization'])) {
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
                                    return $organization->getName() . ' ' . ($organization->getScan() ? '📷' : '🔒');
                                },
                                'multiple' => false,
                                'expanded' => false,
                                'required' => false,
                                'placeholder' => '',
                                'attr' => array(
                                    'class' => 'organization-ajax-filter-select'
                                )
                            ]);
                        }
                    }
                }
            );
        if ($_ENV['USE_BARCODES']) {
            $builder->add('barcodeNumber', TextType::class, ['required' => false]);
        }
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
