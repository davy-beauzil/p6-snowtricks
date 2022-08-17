<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Services\Emails\EmailService;
use App\Services\SecurityService;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'email:send', description: 'Add a short description for your command',)]
class EmailSendCommand extends Command
{
    public function __construct(
        private EmailService $emailService,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = new User();
            $user->setId(SecurityService::generateRamdomId());
            $user->setConfirmationToken(SecurityService::generateRamdomId());
            $user->setEmail('davy.beauzil72@gmail.com');
            $user->setUsername('davy');
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());

            $this->emailService->sendAccountConfirmationEmail($user);
            $io->success('Message envoyé !');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
