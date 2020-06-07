<?php

namespace App\Form\Type;

use App\Entity\Achievement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'label'     => false,
                'required'  => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Rule Name'),

            ))
            ->add('rule', CollectionType::class, [
                'label'     =>  false,
                'entry_type' => RuleType::class,
                'entry_options' => [
                    'attr' => ['class' => 'rule'],
                ],
                'allow_add' =>  true,
                'data'      => [[]]
            ]);
        $builder->add('save', SubmitType::class, [
            'attr' => ['class' => 'btn btn-success'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Achievement::class,
        ]);
    }
}
