<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserAvatarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'label' => false,
                'attr' => [
                    'accept' => "image/jpeg, image/png",
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'maxSizeMessage' => 'Макимальный размер изображения 1Мб',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Загрузите изображение формата JPEG или PNG.',
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Загрузить',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }
}
