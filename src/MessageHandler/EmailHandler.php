<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EmailHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Email $email): void
    {
        $templatedEmail = $email->getTemplatedEmail();
        try {
            $this->logger->info('Envoi email | ' . $templatedEmail->getSubject());
            $this->mailer->send($templatedEmail);
            $this->logger->info('Email envoyé avec succès');
        } catch (TransportExceptionInterface $e) {
            $this->logger->critical('Email non envoyé : ' . $e->getMessage());
        }
    }
}
