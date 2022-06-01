<?php

declare(strict_types=1);

namespace App\Form\ResetPassword;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordHandler {

    public function __construct(
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $doctrine,
        private UserRepository $userRepository,
    )
    {}

    public function prepare(User $user, array $options = []): FormInterface
    {
        return $this->formFactory->create(ResetPasswordForm::class, $user, $options);
    }

    public function handle(FormInterface $form, Request $request, UserPasswordHasherInterface $passwordHasher): ?bool
    {
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var User $data */
            $data = $form->getData();
            try {
                $user = $this->userRepository->findOneById($data->getId());
                if(!$data->checkStrengthPassword()){
                    $form->addError(new FormError('Votre mot de passe doit faire au moins 8 caractères et comporter un chiffre et un caractère spécial (@ - _ / ou !).'));
                    return false;
                }
                $em = $this->doctrine->getManager();
                $user->setPassword($passwordHasher->hashPassword($user, $data->getPassword()));
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setForgotPasswordToken(bin2hex(random_bytes(64)));
                $em->persist($user);
                $em->flush();
                return true;
            } catch (\Throwable $e) {
                dd($e->getMessage());
                $form->addError(new FormError('Une erreur s’est produite lors de la modification de votre mot de passe.'));
                return false;
            }
        }
        return null;
    }

}