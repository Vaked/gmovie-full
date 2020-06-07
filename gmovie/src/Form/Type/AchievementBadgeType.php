<?php

namespace App\Form\Type;

use App\Entity\Achievement;
use App\Entity\AchievementBadge;
use App\Entity\Badge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\BadgeRepository;
use App\Repository\AchievementRepository;

class AchievementBadgeType extends AbstractType
{
    private $badgeRepository;
    private $achievementRepository;

    public function __construct(BadgeRepository $badgeRepository, AchievementRepository $achievementRepository)
    {
        $this->badgeRepository = $badgeRepository;
        $this->achievementRepository = $achievementRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('badge', EntityType::class, [
                'label'        =>   false,
                'placeholder'  =>   'Select a badge',
                'class'        =>   Badge::class,
                'choices'      =>   $this->badgeRepository->findBy(
                    array('isActive' => 1)
                ),
                'choice_label' =>   function (Badge $badge) {
                    return sprintf('(%s)', $badge->getName());
                },
                'attr'         =>   array('class' => 'form-control')
            ])
            ->add('achievement', EntityType::class, [
                'label'        =>   false,
                'placeholder'  =>   'Select a rule',
                'class'        =>   Achievement::class,
                'choices'      =>   $this->achievementRepository->findBy(
                    array('isActive' => 1)
                ),
                'choice_label' =>   function (Achievement $achievement) {
                    return sprintf('(%s)', json_encode($achievement->getName()));
                },
                'attr'         =>   array('class' => 'form-control')
            ])->add('save', SubmitType::class, [
                'attr'         => ['class' => 'btn btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AchievementBadge::class,
        ]);
    }
}
