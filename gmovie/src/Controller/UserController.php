<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Repository\UserAchievementRepository;
use App\Repository\UserMovieRepository;
use App\Form\Type\EditUserType;
use App\Entity\UserProfile;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;

/**
 * Require ROLE_USER for *every* controller method in this class.
 *
 * @IsGranted("ROLE_USER")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     * 
     */
    public function index()
    {
        return $this->render(
            'profile/index.html.twig',
            array()
        );
    }

    /**
     * @Route("/profile/edit", name="edit")
     */
    public function editUserProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserProfileRepository $repository
    ) {
        $userProfile = $repository->findOneBy(array('user' => $this->getUser()));
        $form = $this->createForm(EditUserType::class, $userProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();
        }

        return $this->render(
            'profile/edit_user.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $userProfile
            )
        );
    }

    /**
     * @Route("/profile/", name="profile")
     */
    public function profile(
        UserMovieRepository $userMovieRepository,
        UserAchievementRepository $userAchievementRepository
    ) {
        $movieHistory = $userMovieRepository->findBy(
            array(
                'user' => $this->getUser(),
                'isWatched' => 1
            )
        );
        $userAchievements = $userAchievementRepository->findBy(
            array(
                'user' => $this->getUser()
            )
        );
        $achievements = array_map(function ($userAchievements) {
            return $userAchievements->getAchievementBadge();
        }, $userAchievements);
        return $this->render(
            'profile/index.html.twig',
            array(
                'history' => $movieHistory,
                'achievements' => $achievements
            )
        );
    }
}
