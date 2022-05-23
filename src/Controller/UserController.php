<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Login\LoginHandler;
use App\Form\Register\RegisterHandler;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function __construct(
        private RegisterHandler $registerHandler,
        private UserRepository $userRepository,
    )
    {}

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->registerHandler->prepare($user);
        $registered = $this->registerHandler->handle($form, $request, $passwordHasher);

        if($registered !== null){

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

}
