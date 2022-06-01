<?php

namespace App\Form\ForgotPassword;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ForgotPasswordHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UserRepository $userRepository,
    )
    {}

    public function prepare(User $user, array $options = []): FormInterface
    {
        return $this->formFactory->create(ForgotPasswordForm::class, $user, $options);
    }

    public function handle(FormInterface $form, Request $request): ?User
    {
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var User $user */
            $user = $form->getData();
            $user = $this->userRepository->findOneByUsername($user->getUserIdentifier());
            if(!$user instanceof User){
                throw new BadCredentialsException();
            }
            return $user;
        }
        return null;
    }
}