<?php

namespace App\Form\Type;

use App\Entity\Template;
use App\Service\TPLNotifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TemplateType extends AbstractType
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
                'attr' => array('class' => 'form-control', 'placeholder' => 'Template Name'),
                'empty_data' => null,
            ))

            ->add('content', TextareaType::class, array(
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Enter your template HTML here'),

            ))
            ->add('rule', ChoiceType::class, array(
                'choices' => [
                    'Every Day' => Template::ED,
                    'Beginning Of Month' => Template::BOM,
                    'Every Week' => Template::EW,
                    'Every Two Weeks' => Template::E2W,
                    'Every Three Weeks' => Template::E2W,
                    'End Of Month' => Template::EOM,
                ],
                'required' => true,
                'attr' => array('class' => 'form-control'),

            ))
            ->add('executionFunction', ChoiceType::class, array(
                'choices' => $this->getTemplateMethods(),
                'required' => true,
                'attr' => array('class' => 'form-control'),

            ));
        $builder->add('save', SubmitType::class, [
            'attr' => ['class' => 'btn btn-secondary btn-sm submit save'],
        ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Template::class,
        ]);
    }

    private function getTemplateMethods()
    {
        $templateMethods = array();
        $mailerMethods = get_class_methods(TPLNotifier::class);
        $mailerMethods = array_filter($mailerMethods, function ($mailerMethod) {
            if (strpos($mailerMethod, 'tpl') !== false) {
                return $mailerMethod;
            }
        });
        $templateMethods = array_combine($mailerMethods, $mailerMethods);
        return $templateMethods;
    }
}
