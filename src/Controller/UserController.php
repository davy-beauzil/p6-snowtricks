<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPassword\ForgotPasswordHandler;
use App\Form\Register\RegisterHandler;
use App\Form\ResetPassword\ResetPasswordHandler;
use App\Repository\UserRepository;
use App\Services\Emails\EmailService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function __construct(
        private RegisterHandler  $registerHandler,
        private ForgotPasswordHandler $forgotPasswordHandler,
        private UserRepository        $userRepository,
        private EmailService          $emailService,
        private ResetPasswordHandler $resetPasswordHandler,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {}

    #[Route('/register', name: 'register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->registerHandler->prepare($user);
        $registered = $this->registerHandler->handle($form, $request, $this->passwordHasher);

        if($registered instanceof User){
            $this->emailService->sendAccountConfirmationEmail($registered);
            $this->addFlash('success', 'Un email vous a été envoyé pour activer votre compte.');
            return $this->redirectToRoute('login');
        }

        return $this->renderForm('user/register.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->renderForm('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/user/confirm/{user_id}/{confirmation_token}', name: 'user_confirm', methods: 'GET')]
    public function confirm(string $user_id, string $confirmation_token): Response
    {
        try {
            $user = $this->userRepository->findOneById($user_id);
            if($user === null){
                throw new Exception();
            }
            if($user->getConfirmationToken() === $confirmation_token){
                $this->userRepository->confirm($user);
            }
            $this->addFlash('success', 'Votre compte a bien été validé.');
            return $this->redirectToRoute('login');
        } catch (Exception) {
            $this->addFlash('danger', 'Une erreur s’est produite durant la validation de votre compte.');
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/forgot_password', name: 'forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request): Response
    {
        $user = new User();
        $form = $this->forgotPasswordHandler->prepare($user, []);
        try{
            /** @var User $user */
            $user = $this->forgotPasswordHandler->handle($form, $request);
            if($user !== null){
                $this->emailService->sendResetPasswordEmail($user);
                $this->addFlash('success', 'Un email vous a été envoyé pour que vous puissiez changer de mot de passe');
                return $this->redirectToRoute('login');
            }
        }catch(BadCredentialsException){
            $form->addError(new FormError('Aucun utilisateur ne correspond à ce nom d’utilisateur.'));
        }
        return $this->renderForm('user/forgot_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reset_password/{user_id}/{forgot_password_token}', name: 'reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(string $user_id, string $forgot_password_token, Request $request): Response
    {
        try {
            $user = $this->userRepository->findOneById($user_id);
            if($user === null){
                throw new Exception();
            }
            if($user->getForgotPasswordToken() !== $forgot_password_token){
                throw new Exception();
            }
            $user = new User();
            $form = $this->resetPasswordHandler->prepare($user, []);
            $passwordIsUpdated = $this->resetPasswordHandler->handle($form, $request, $this->passwordHasher);

            if(false === $passwordIsUpdated){
                $this->addFlash('danger', 'Une erreur s’est produite durant la modification de votre mot de passe');
            }elseif(true === $passwordIsUpdated){
                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
                return $this->redirectToRoute('login');
            }
        } catch (Exception) {
            $this->addFlash('danger', 'Une erreur s’est produite, veuillez refaire une demande.');
            return $this->redirectToRoute('forgot_password');
        }
        return $this->renderForm('user/reset_password.html.twig', [
            'form' => $form,
            'user_id' => $user_id,
        ]);
    }

}
