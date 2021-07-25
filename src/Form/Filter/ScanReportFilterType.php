<?php

namespace App\Form\Filter;

use App\Entity\Location;
use App\Entity\ScanPlan;
use App\Repository\LocationRepository;
use App\Repository\ScanPlanRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScanReportFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => false])
            ->add('location', EntityType::class, [
                'class' => 'App:Location',
                'query_builder' => function (LocationRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC')
                        ;
                },
                'choice_label' => function (Location $location) {
                    return $location->getName();
                },
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => '',
                'attr' => array(
                    'class' => 'location-select'
                )
            ])
            ->add('scanPlan', EntityType::class, [
                'class' => ScanPlan::class,
                'query_builder' => function (ScanPlanRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.name', 'ASC')
                        ;
                },
                'choice_label' => function (ScanPlan $scanPlan) {
                    return $scanPlan->getName();
                },
                'choice_translation_domain' => null,
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => '',
                'attr' => array(
                    'class' => ''
                )
            ])
        ;
        $builder->add('orderedFrom', DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
        $builder->add('orderedTill', DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
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
