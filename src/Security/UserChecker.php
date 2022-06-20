<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }
        if ($user->getConfirmedAt() === null) {
            throw new CustomUserMessageAccountStatusException(
                'Vous devez activer votre compte pour pouvoir vous connecter.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
