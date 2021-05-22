<?php

namespace App\Form;

use App\Entity\Letter;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

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
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC')
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
            ->add('status')
            ->add('file', FileType::class, [
                'label' => 'Scan (PDF file)',

                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Letter::class,
        ]);
    }
}
