<?php

namespace App\Controller;

use App\Entity\Achievement;
use App\Form\Type\AchievementType;
use App\Repository\AchievementBadgeRepository;
use App\Repository\AchievementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Require ROLE_ADMIN for *every* controller method in this class.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class RuleController extends AbstractController
{
    /**
     * @Route("/rule/delete/{ruleId}", name="rule_delete")
     */
    public function ruleDelete(
        Request $request,
        EntityManagerInterface $em,
        $ruleId,
        AchievementBadgeRepository $achievementBadgeRepository,
        AchievementRepository $achievementRepository
    ) {
        if (isset($ruleId) && $request->isXmlHttpRequest()) {
            $rule = $achievementRepository->find($ruleId);
            if ($rule) {
                $achievementBadge = $achievementBadgeRepository->findBY(
                    array("achievement" => $ruleId)
                );
                if ($achievementBadge) {
                    foreach ($achievementBadge as $achievement) {
                        $em->remove($achievement);
                    }
                }
                $em->remove($rule);
                $em->flush();
                return new JsonResponse(
                    array(
                        'status' => 'OK'
                    ),
                    200
                );
            }
            return new JsonResponse(
                array(
                    'status' => 'Error',
                    'message' => 'Error'
                ),
                400
            );
        }
    }

    /**
     * @Route("/rule/edit/{rule}", name="rule_edit")
     */
    public function ruleEdit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        Achievement $rule
    ) {
        $formAchievement = $this->createForm(AchievementType::class, $rule);
        $formAchievement->handleRequest($request);
        if ($formAchievement->isSubmitted() && $formAchievement->isValid()) {
            $rule->setDate(new \DateTime('now'));
            $entityManager->flush();
        } else {
            $errors = $validator->validate($formAchievement);
        }
        return $this->render('admin/edit_rule.html.twig', [
            'formAchievement' => $formAchievement->createView(),
            'rule' => $rule,
            'errors' => $errors ?? null
        ]);
    }

    /**
     * @Route("/rule/activate/{rule}", name="rule_activate")
     */
    public function badgeActivate(Achievement $rule, EntityManagerInterface $entityManager)
    {
        if ($rule->getIsActive()) {
            $rule->setIsActive(0);
        } else {
            $rule->setIsActive(1);
        }
        $entityManager->persist($rule);
        $entityManager->flush();
        return $this->redirectToRoute('edit_achievements');
    }
}
