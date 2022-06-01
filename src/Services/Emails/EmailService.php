<?php

declare(strict_types=1);

namespace App\Services\Emails;

use App\Entity\User;
use App\Message\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {
    }

    public function sendAccountConfirmationEmail(User $user): void
    {
        $templatedEmail = (new TemplatedEmail())
            ->from('noreply-p6@davy-beauzil.fr')
            ->to(new Address($user->getEmail()))
            ->subject('Activez votre compte Snowtricks')
            ->htmlTemplate('emails/user_confirmation.html.twig')
            ->context([
                'user' => $user,
                'confirmation_token' => $user->getConfirmationToken(),
                'app_url' => $_ENV['APP_URL'],
            ])
        ;
        $this->bus->dispatch(new Email($templatedEmail));
    }

    public function sendResetPasswordEmail(User $user): void
    {
        $templatedEmail = (new TemplatedEmail())
            ->from('noreply-p6@davy-beauzil.fr')
            ->to(new Address($user->getEmail()))
            ->subject('Changez votre mot de passe')
            ->htmlTemplate('emails/reset_password.html.twig')
            ->context([
                'user' => $user,
                'forgot_password_token' => $user->getForgotPasswordToken(),
                'app_url' => $_ENV['APP_URL'],
            ])
        ;
        $this->bus->dispatch(new Email($templatedEmail));
    }
}
