<?php

namespace App\Controller;

use App\Repository\AchievementBadgeRepository;
use App\Repository\BadgeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\Type\BadgeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Badge;

/**
 * Require ROLE_ADMIN for *every* controller method in this class.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class BadgeController extends AbstractController
{

    /**
     * @Route("/badge/delete/{badgeId}", name="badge_delete")
     */
    public function badgeDelete(
        Request $request,
        EntityManagerInterface $em,
        $badgeId,
        AchievementBadgeRepository $achievementBadgeRepository,
        BadgeRepository $badgeRepository
    ) {
        if (isset($badgeId) && $request->isXmlHttpRequest()) {
            $badge = $badgeRepository->find($badgeId);
            if ($badge) {
                $achievementBadge = $achievementBadgeRepository->findBY(
                    array("badge" => $badgeId)
                );
                if ($achievementBadge) {
                    foreach ($achievementBadge as $achievement) {
                        $em->remove($achievement);
                    }
                }
                $em->remove($badge);
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
     * @Route("/badge/edit/{badge}", name="badge_edit")
     */
    public function badgeEdit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        Badge $badge
    ) {
        $formBadge = $this->createForm(BadgeType::class, $badge);
        $formBadge->handleRequest($request);
        if ($formBadge->isSubmitted() && $formBadge->isValid()) {
            $image = $formBadge['image']->getData();
            $image->move('assets', $image->getClientOriginalName());
            $badge->setImgUrl("/assets/{$image->getClientOriginalName()}");
            $entityManager->flush();
        } else {
            $errors = $validator->validate($formBadge);
        }
        return $this->render('admin/edit.html.twig', [
            'formBadge' => $formBadge->createView(),
            'badge' => $badge,
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/badge/activate/{badge}", name="badge_activate")
     */
    public function badgeActivate(Badge $badge, EntityManagerInterface $entityManager)
    {
        if ($badge->getIsActive()) {
            $badge->setIsActive(0);
        } else {
            $badge->setIsActive(1);
        }
        $entityManager->persist($badge);
        $entityManager->flush();
        return $this->redirectToRoute('edit_achievements');
    }
}
