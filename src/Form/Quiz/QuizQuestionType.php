<?php

namespace App\Form\Quiz;

use App\Entity\Question;
use App\Entity\QuizQuestion;
use Doctrine\ORM\EntityRepository;
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
                'query_builder' => function (EntityRepository $er) use ($options) {
                        return $er->createQueryBuilder('q')
                            ->where('q.user = ?1')
                            ->setParameter(1, $options['user']);
                    },
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
            ],
        ]);

        $resolver->setRequired([
            'user',
        ]);
    }
}
