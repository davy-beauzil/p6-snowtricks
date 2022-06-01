<?php

declare(strict_types=1);

namespace App\Form\Login;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class LoginHandler
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private ManagerRegistry $doctrine,
    ) {
    }

    public function prepare(User $user, array $options = []): FormInterface
    {
        return $this->formFactory->create(LoginForm::class, $user, $options);
    }

    public function handle(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            try {
                $em = $this->doctrine->getManager();
                $user->setId(bin2hex(random_bytes(64)));
                $user->setCreatedAt(new DateTimeImmutable());
                $user->setUpdatedAt(new DateTimeImmutable());
                $em->persist($user);
                $em->flush();
            } catch (Throwable) {
                $form->addError(new FormError('Une erreur s’est produite lors de la création de votre compte'));

                return false;
            }

            return true;
        }

        return false;
    }
}
