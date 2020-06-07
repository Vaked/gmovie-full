<?php

namespace App\Form\Type;

use App\Entity\Badge;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\DataMapperInterface;


class BadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'         => false,
                'required'      => true,
                'attr'          => array('class' => 'form-control', 'placeholder' => 'Badge Name'),
            ))
            ->add('image', FileType::class, [
                'label'         =>  'Badge Image:',
                'required'      =>  true,
                'constraints'   =>  [
                    new File([
                        'maxSize'   =>  '1024k',
                        'mimeTypes' =>  [
                            'image/png',
                        ]
                    ])
                ],
                'attr'          =>  array('class' => 'file-path validate', 'accept' => 'image/png')
            ])->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }
}
