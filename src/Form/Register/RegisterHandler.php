<?php

declare(strict_types=1);

namespace App\Form\Register;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterHandler {

    public function __construct(
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $doctrine,
    )
    {}

    public function prepare(User $user, array $options = []): FormInterface
    {
        return $this->formFactory->create(RegisterForm::class, $user, $options);
    }

    public function handle(FormInterface $form, Request $request, UserPasswordHasherInterface $passwordHasher): ?User
    {
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var User $user */
            $user = $form->getData();

            if(!$user->checkStrengthPassword()){
                $form->addError(new FormError('Votre mot de passe doit faire au moins 8 caractères et comporter un chiffre et un caractère spécial (@ - _ / ou !).'));
                return null;
            }

            try{
                $em = $this->doctrine->getManager();
                $user->setId(bin2hex(random_bytes(64)));
                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setUpdatedAt(new \DateTimeImmutable());
                $plainPassword = $user->getPassword();
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                $user->setConfirmationToken(bin2hex(random_bytes(64)));
                $user->setForgotPasswordToken(bin2hex(random_bytes(64)));
                $em->persist($user);
                $em->flush();
                return $user;
            }catch(\Throwable $e){
                $form->addError(new FormError('Une erreur s’est produite lors de la création de votre compte'));
                return null;
            }
        }
        return null;
    }

}