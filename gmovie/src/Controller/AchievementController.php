<?php

namespace App\Controller;

use App\Repository\AchievementBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\AchievementBadge;
use App\Form\Type\AchievementBadgeType;
use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AchievementController extends AbstractController
{
    /**
     * @Route("/achievement/delete/{achievementId}", name="achievement_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function achievementDelete(
        Request $request,
        EntityManagerInterface $em,
        $achievementId,
        AchievementBadgeRepository $achievementBadgeRepository
    ) {
        if (isset($achievementId) && $request->isXmlHttpRequest()) {
            $achievement = $achievementBadgeRepository->find($achievementId);
            if ($achievement) {
                $em->remove($achievement);
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
     * @Route("/achievement/edit/{achievement}", name="achievement_edit")
     * @IsGranted("ROLE_ADMIN")
     */
    public function ruleEdit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        AchievementBadge $achievement
    ) {
        $achievementBadge = $this->createForm(AchievementBadgeType::class, $achievement);
        $achievementBadge->handleRequest($request);
        if ($achievementBadge->isSubmitted() && $achievementBadge->isValid()) {
            $achievement->setDate(new \DateTime('now'));
            $entityManager->flush();
        } else {
            $errors = $validator->validate($achievementBadge);
        }
        return $this->render('admin/edit.html.twig', [
            'achievementBadge' => $achievementBadge->createView(),
            'achievement' => $achievement,
            'errors' => $errors ?? null
        ]);
    }


    /**
     * @Route("/achievements", name="achievements")
     */
    public function showAll(
        AchievementBadgeRepository $achievementBadgeRepository,
        UserAchievementRepository $userAchievementRepository
    ) {
        $allAchievements = $achievementBadgeRepository->findAll();
        $userAchievements = $userAchievementRepository->findBy(array(
            'user' => $this->getUser()
        ));
        $userAchievements = array_map(function ($userAchievements) {
            return $userAchievements->getAchievementBadge();
        }, $userAchievements);

        return $this->render('achievements/index.html.twig', [
            'user_achievements' => $userAchievements,
            'achievements' => $allAchievements,
        ]);
    }
}
