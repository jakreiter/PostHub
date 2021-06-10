<?php

namespace App\Form;

use App\Entity\Letter;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class LetterHandoverSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('letters', EntityType::class, [
            'class' => Letter::class,
            'choices' => $options['letters'],
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);
        $builder->add('printoutBtn', SubmitType::class, [
            'label' => 'Prepare a printout of the handover',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
        $builder->add('changeStatusBtn', SubmitType::class, [
            'label' => 'Prepare a printout of the handover and Change status to Given to the recipient',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'se';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'letters' => []
        ]);
    }
}
