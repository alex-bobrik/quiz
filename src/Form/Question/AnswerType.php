<?php

namespace App\Form\Question;

use App\Entity\Answer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => ['class' => 'form-control answer-input',
                    'maxLength' => 250,
                    'placeholder' => 'Ответ (max 250)',
                    ],
            ])
            ->add('isCorrect', CheckboxType::class, [
                'attr' => ['class' => 'checkbox-sized',
                    ],
                'required' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
            'attr' => ['class' => 'form-group answer-box',
                ]
        ]);
    }
}
