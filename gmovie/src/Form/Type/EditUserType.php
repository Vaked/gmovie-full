<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\UserProfile;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class EditUserType extends AbstractType
{
    private $container;

    public function __construct(ContainerBagInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('name', TextType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'First Name'),
                'empty_data' => null,
            ))

            ->add('surname', TextType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Last Name'),

            ))
            ->add('country', ChoiceType::class, array(
                'choices' => Yaml::parseFile(__DIR__ . $this->container->get('countries_location')),
                'choice_attr' => [
                    'Country' => [
                        'value' => "",
                        'selected' => true,
                        'disabled' => true,
                        'hidden' => true
                    ]
                ],
                'required' => true,
                'attr' => array('class' => 'form-control'),

            ))
            ->add('city', TextType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'City'),

            ))
            ->add('address', TextType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Address'),

            ));
        $builder->add('save', SubmitType::class, [
            'attr' => ['class' => 'btn btn-secondary btn-sm submit save'],
        ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
