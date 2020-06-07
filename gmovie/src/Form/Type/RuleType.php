<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RuleType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', IntegerType::class, array(
                'label'     => false,
                'required'  => true,
                'attr'      => array('class' => 'form-control', 'placeholder' => 'Count*'),

            ))
            ->add('type', ChoiceType::class, array(
                'label'          => false,
                'choices'       => array(
                    'Type'      =>  'Type',
                    'Genre'     => 'Genre',
                    'Director'  => 'Director',
                    'Year'      => 'Year',
                ),
                'attr'          =>  array('class' => 'form-control'),
                'choice_attr' => [
                    'Type' => [
                        'value' => "",
                        'selected' => true,
                        'disabled' => true,
                        'hidden' => true
                    ]
                ]
            ))
            ->add('value', TextType::class, array(
                'label'     => false,
                'required'  => false,
                'attr'      => array('class' => 'form-control', 'placeholder' => 'Type Value'),

            ));
    }
}
