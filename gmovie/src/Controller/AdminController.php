<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
use App\Form\Type\AchievementBadgeType;
use App\Form\Type\AchievementType;
use App\Form\Type\BadgeType;
use App\Form\Type\TemplateType;
use App\Repository\AchievementBadgeRepository;
use App\Repository\AchievementRepository;
use App\Repository\BadgeRepository;
use App\Repository\TemplateRepository;
use App\Service\Mailer;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Require ROLE_ADMIN for *every* controller method in this class.
 *
 * @IsGranted("ROLE_ADMIN")
 */

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin")
     */
    public function index()
    {


        return $this->render(
            'admin/index.html.twig',
            array()
        );
    }

    /**
     * @Route("admin/edit/users", name="edit_users")
     */
    public function getEditUsers()
    {

        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        return $this->render(
            'admin/edit_users.html.twig',
            array('users' => $users)
        );
    }

    /**
     * @Route("admin/edit/templates", name="edit_templates")
     */
    public function editTemplates(Request $request, EntityManagerInterface $entityManager, TemplateRepository $templateRepository)
    {
        $templateForm = $this->createForm(TemplateType::class);
        $templateForm->handleRequest($request);
        if ($templateForm->isSubmitted() && $templateForm->isValid()) {
            $template = $templateForm->getData();
            $entityManager->persist($template);
            $entityManager->flush();
        }
        return $this->render('admin/template.html.twig', array(
            'templateForm' => $templateForm->createView(),
            'templates'    => $templateRepository->findAll()
        ));
    }

    /**
     * @Route("admin/edit/achievements", name="edit_achievements")
     */
    public function getEditAchievement(
        Request $request,
        EntityManagerInterface $entityManager,
        BadgeRepository $badgeRepository,
        ValidatorInterface $validator,
        AchievementRepository $achievementRepository,
        AchievementBadgeRepository $achievementBadgeRepository
    ) {
        $formBadge              = $this->createForm(BadgeType::class);
        $formAchievement        = $this->createForm(AchievementType::class);
        $formAchievementBadge   = $this->createForm(AchievementBadgeType::class);
        $formBadge->handleRequest($request);
        if ($formBadge->isSubmitted() && $formBadge->isValid()) {
            $badge = $formBadge->getData();
            $badge->image->move('assets', $badge->image->getClientOriginalName());
            $badge->setImgUrl("/assets/{$badge->image->getClientOriginalName()}")
                ->setIsActive(0);
            $entityManager->persist($badge);
        } else {
            $errors = $validator->validate($formBadge);
        }
        $formAchievement->handleRequest($request);
        if ($formAchievement->isSubmitted() && $formAchievement->isValid()) {
            $achievement = $formAchievement->getData();
            $achievement->setDate(new \DateTime('now'))
                ->setIsActive(0);
            $entityManager->persist($achievement);
        } else {
            $errors = $validator->validate($formAchievement);
        }
        $formAchievementBadge->handleRequest($request);
        if ($formAchievementBadge->isSubmitted() && $formAchievementBadge->isValid()) {
            $achievementBadge = $formAchievementBadge->getData();
            $achievementBadge->setDate(new \DateTime('now'));
            $entityManager->persist($achievementBadge);
        } else {
            $errors = $validator->validate($formAchievementBadge);
        }
        $entityManager->flush();
        return $this->render(
            'admin/edit_achievements.html.twig',
            [
                'formBadge'        =>   $formBadge->createView(),
                'formAchievement'  =>   $formAchievement->createView(),
                'achievementBadge' =>   $formAchievementBadge->createView(),
                'badges'           =>   $badgeRepository->findAll(),
                'rules'            =>   $achievementRepository->findAll(),
                'achievements'     =>   $achievementBadgeRepository->findAll(),
                'errors'           =>   $errors ?? null
            ]

        );
    }

    /**
     * @Route("/admin_reset_password/{id}", name="admin_reset_password")
     */
    public function adminResetPassword(
        $id,
        Request $request,
        UserRepository $userRepository,
        Mailer $mailer,
        EntityManagerInterface $entityManager
    ) {
        $user = $userRepository->findOneBy([
            'id' => $id
        ]);

        $flashBag = $this->get('session')->getFlashBag();

        if ($user) {
            $newCode = $userRepository->generatеResetCode($user);

            if (!$newCode) {
                $flashBag->get('error'); // gets message and clears type
                $flashBag->set('error', 'Error  generate code');

                return $this->redirectToRoute('edit_users');
            }

            $user->setResetCode($newCode);
            $entityManager->persist($user);
            $entityManager->flush();

            $renderedTemplate = $this->renderView('login/email_reset_password.html.twig', [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'resetCode' => $newCode->getCode()
            ]);

            $mailer->notifyUser($user, $renderedTemplate);

            $flashBag->get('success'); // gets message and clears type
            $flashBag->set('success', 'Send mail to user ' . $user->getEmail() . '');

            return $this->redirectToRoute('edit_users');
        }

        $flashBag->get('error'); // gets message and clears type
        $flashBag->set('error', 'User not found');

        return $this->redirectToRoute('edit_users');
    }
}
