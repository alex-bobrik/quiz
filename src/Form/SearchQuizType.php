<?php

namespace App\Form;

use App\Entity\QuizCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchQuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', TextType::class, [
                'label' => '',
                'required' => false,
                'attr' => [
                ]
            ])
            ->add('categories', EntityType::class, [
                'label' => 'Категория',
                'class' => QuizCategory::class,
                'choice_label' => 'name',
                'mapped' => true,
                'multiple' => true,
                'expanded' => true,
                'attr' => [
//                    'class' => 'form-control',
                ]
            ])
            ->add('timeLimit', ChoiceType::class, [
                'choices' => [
                    'Ограничена' => 'limit',
                    'Не ограничена' => 'no-limit',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Search',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
