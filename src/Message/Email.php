<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class Email
{
    public function __construct(private TemplatedEmail $templatedEmail)
    {
    }

    public function getTemplatedEmail(): TemplatedEmail
    {
        return $this->templatedEmail;
    }
}
