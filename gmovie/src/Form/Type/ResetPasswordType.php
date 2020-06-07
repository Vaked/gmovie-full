<?php
namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('plainPassword', RepeatedType::class, array(
            'type' => PasswordType::class,
            'constraints' => [new Assert\Length(['min' => 6])],
            'first_options'  => array('label' => false, 'attr' => array(
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Password',
            )),
            'second_options' => array('label' => false, 'attr' => array(
                'class' => 'form-control',
                'required' => true,
                'placeholder' => 'Repeat Password',
            )),
        ));
        $builder->add('save', SubmitType::class, [
            'attr' => ['class' => 'save'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}