<?php

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
    ){}


    public function __invoke(Email $email)
    {
        try {
            $this->logger->critical('1111111111111111111111111111111111111111111111111111111111111111111111');
            $this->mailer->send($email->getTemplatedEmail());
            $this->logger->critical('2222222222222222222222222222222222222222222222222222222222222222222222');
        } catch (TransportExceptionInterface $e) {
            $this->logger->critical('3333333333333333333333333333333333333333333333333333333333333333333333');
            $this->logger->critical($e->getMessage());
        }
    }
}