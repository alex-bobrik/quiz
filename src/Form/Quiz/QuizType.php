<?php

namespace App\Form\Quiz;

use App\Entity\Quiz;
use App\Entity\QuizCategory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('image', FileType::class, [
                'help' => 'Выберите изображение для викторины',
                'label' => false,
                'attr' => [
                    'accept' => "image/jpeg, image/png",
//                    'class' => 'form-control',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'maxSizeMessage' => 'Макимальный размер изображения 1Мб',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Загрузите изображение формата JPEG или PNG.',
                    ])
                ]
            ])
            ->add('quizCategory', EntityType::class, [
                'class' => QuizCategory::class,
                'choice_label' => 'name',
                'mapped' => true,
                'multiple' => false,
                'attr' => ['class'=> 'form-control']
            ])
            ->add('questions', CollectionType::class, [
                'entry_type' => QuizQuestionType::class,
                'entry_options' => [
                    'user' => $options['user'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'data_class' => null,
                'by_reference' => false,
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Save the quiz'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
            'attr' => ['id' => 'quiz_form'],
        ]);

        $resolver->setRequired([
            'user',
        ]);
    }
}
