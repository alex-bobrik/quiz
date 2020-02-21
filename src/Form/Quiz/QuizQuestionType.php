<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\QuizQuestion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'choice_label' => 'text',
                'mapped' => true,
                'multiple' => false,
                'attr' => ['class'=> 'selectpicker form-control', 'data-live-search'=>'true']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => QuizQuestion::class,
            'attr' => ['class' => 'form-group answer-box',
            ]
        ]);
    }
}
