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

class LetterOrderScanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Letter::class,
        ]);
    }
}
