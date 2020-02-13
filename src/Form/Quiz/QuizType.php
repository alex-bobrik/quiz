<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('questions', CollectionType::class, [
                'entry_type' => QuizQuestionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'data_class' => null,
                'by_reference' => false,
            ])
            ->add('submit', ButtonType::class, ['label' => 'Save the quiz'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'attr' => ['id' => 'quiz_form'],
        ]);
    }
}
