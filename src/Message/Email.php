<?php

namespace App\Message;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class Email
{
    private TemplatedEmail $templatedEmail;

    public function __construct(TemplatedEmail $templatedEmail)
    {
        $this->templatedEmail = $templatedEmail;
    }

    public function getTemplatedEmail(): TemplatedEmail
    {
        return $this->templatedEmail;
    }
}