<?php

namespace App\Form\Type;

use App\Entity\User;
use App\Entity\UserProfile;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class UserType extends AbstractType implements DataMapperInterface
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
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'First Name'),
                'empty_data' => null,
            ))

            ->add('surname', TextType::class, array(
                'label' => false,
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
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control'),

            ))
            ->add('city', TextType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'City'),

            ))
            ->add('address', TextType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Address'),

            ))
            ->add('birthday', DateType::class, array(
                'label' => false,
                'required' => true,
                'html5' => false,
                'widget' => 'single_text',
                'attr' => array('id' => 'birthday', 'name' => 'birthday', 'class' => 'date-picker form-control has-feedback-left', 'placeholder' => 'Birthday'),

            ))

            ->add('email', EmailType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Email'),

            ))
            ->add('username', TextType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array('class' => 'form-control', 'placeholder' => 'Username'),

            ))
            ->add('receiveAdvertisement', CheckboxType::class, [
                'label'    => false,
                'required' => false,
            ])
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
                'constraints' => [new Length(['min' => 6])]
            ))

            // configure the data mapper for this FormType
            ->setDataMapper($this);

        $builder->add('save', SubmitType::class, [
            'attr' => ['class' => 'save'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'empty_data', null
        ));
    }

    /**
     * @param User|null $viewData
     */
    public function mapDataToForms($viewData, $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        // invalid data type
        if (!$viewData instanceof User) {
            throw new UnexpectedTypeException($viewData, User::class);
        }

        if (!in_array($viewData->getUserProfile()->getCountry(), Yaml::parseFile(__DIR__ . '/../../../config/countries.yaml'))) {
            throw new Exception("Undefined Country");
        }

        if (!filter_var($viewData->getemail(), FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        // initialize form field values
        if ($viewData->getReceiveAdvertisement()) {
            $forms['receiveAdvertisement']->setData($viewData->getReceiveAdvertisement());
        } else {
            $forms['receiveAdvertisement']->setData(false);
        }
        $forms['name']->setData($viewData->getUserProfile()->getName());
        $forms['surname']->setData($viewData->getUserProfile()->getSurname());
        $forms['country']->setData($viewData->getUserProfile()->getCountry());
        $forms['city']->setData($viewData->getUserProfile()->getCity());
        $forms['address']->setData($viewData->getUserProfile()->getAddress());
        $forms['birthday']->setData($viewData->getUserProfile()->getBirthday());
        $forms['email']->setData($viewData->getemail());
        $forms['username']->setData($viewData->getUsername());
        $forms['plainPassword']->setData($viewData->getPlainPassword());
    }
    public function mapFormsToData($forms, &$viewData)
    {

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $user = new User();
        $user->setEmail($forms['email']->getData());
        $user->setPlainPassword($forms['plainPassword']->getData());
        $user->setUsername($forms['username']->getData());
        $user->setReceiveAdvertisement($forms['receiveAdvertisement']->getData());

        $userInfo = new UserProfile();
        $userInfo->setName($forms['name']->getData());
        $userInfo->setSurname($forms['surname']->getData());
        $userInfo->setAddress($forms['address']->getData());
        $userInfo->setCountry($forms['country']->getData());
        $userInfo->setCity($forms['city']->getData());
        $userInfo->setBirthday($forms['birthday']->getData());

        $user->setUserProfile($userInfo);

        $viewData = $user;
    }
}
