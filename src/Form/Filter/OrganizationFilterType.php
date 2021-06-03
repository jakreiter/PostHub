<?php

namespace App\Form\Filter;

use App\Entity\Location;
use App\Repository\LocationRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganizationFilterType extends AbstractType
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
                        ->setMaxResults(5);
                },
                'choice_label' => function (Location $location) {
                    return $location->getName();
                },
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => '',
                'attr' => array(
                    'class' => 'location-ajax-select'
                )
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
