<?php

namespace App\Controller;

use App\Form\Type\UserType;
use App\Form\Type\ResetPasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Security\LoginFormAuthenticator;
use App\Service\Mailer;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Entity\ResetCodes;
use App\Repository\ResetCodeRepository;
use App\Entity\User;

class RegistrationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/register", name="user_registration")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        Mailer $mailer
    ) {
        // 1) build the form

        $form = $this->createForm(UserType::class);

        // 2) handle the submit (will only happen on POST)

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 3) Encode the password (you could also do this via Doctrine listener)
            $user = $userRepository->generatеActivationtCode($form->getData());

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password)
                ->setIsActive(0);
            // 4) save the User!
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $renderedTemplate = $this->renderView('login/email.html.twig', [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'activation_code' => $user->getActivationCode()
            ]);
            $mailer->notifyUser($user, $renderedTemplate);
            if (!$user->getIsActive()) {
                return $this->render(
                    'login/confirm.html.twig',
                );
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return $this->render(
                'login/registration.html.twig',
                array(
                    'form'    => $form->createView(),
                    'errors'  => $form->getErrors()
                )
            );
        }
        return $this->render(
            'login/registration.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/activate/{activationCode}", name="activate")
     */
    public function activate(
        $activationCode,
        Request $request,
        UserRepository $userRepository,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        Mailer $mailer
    ) {

        $user = $userRepository->findOneBy([
            'activationCode' => $activationCode
        ]);
        if ($user) {
            $user->setIsActive(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $renderedTemplate = $this->renderView('getStarted/welcome_email.html.twig', [
                'username' => $user->getEmail()
            ]);
            $mailer->notifyUser($user, $renderedTemplate);
            return $this->render(
                'login/confirm_success.html.twig',
                [$guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
                )]
            );
        } else {
            return $this->render(
                'login/confirm.html.twig'
            );
        }
    }


    /**
     * @Route("/reset_password/{resetCode}", name="reset_password")
     */
    public  function resetPassword(
        $resetCode,
        Request $request,
        ResetCodeRepository $resetCodeRepository
    ) {
        $resetCode = $resetCodeRepository->findOneBy([
            'code' => $resetCode
        ]);

        if ($resetCode && !$resetCode->hasExpired()) {
            $this->entityManager->remove($resetCode);
            $this->entityManager->flush();

            return $this->redirectToRoute('change_password', array('id' => $resetCode->getUser()->getId()));
        }

        return $this->render(
            'profile/change_password.html.twig',
            array(
                'errors'  => 'Reset code is invalid'
            )
        );
    }

    /**
     * @Route("/change_password/", name="change_password")
     */
    public function changePassword(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        Mailer $mailer
    ) {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $user->setIsActive(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $emailBody = $this->renderView('login/change_password_success.html.twig', [
                'username' => $user->getEmail()
            ]);
            $mailer->notifyUser($user, $emailBody);
            return $this->render(
                'login/change_password_success.html.twig',
                [$guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
                )]
            );
        }
        return $this->render(
            'profile/change_password.html.twig',
            array(
                'form'    => $form->createView(),
                'errors'  => $form->getErrors()
            )
        );
    }
}
