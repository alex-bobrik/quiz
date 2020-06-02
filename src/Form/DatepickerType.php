<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatepickerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'label' => 'С',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('end', DateType::class, [
                'label' => 'По',
                'widget' => 'single_text',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Поиск',
                'attr' => [
                    'class' => 'btn btn-success',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form-group',
            ]
        ]);
    }
}
