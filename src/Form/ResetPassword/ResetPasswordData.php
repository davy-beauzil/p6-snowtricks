<?php

declare(strict_types=1);

namespace App\Form\ResetPassword;

class ResetPasswordData
{
    public function __construct(
        public string $id,
        public string $password,
    ) {
    }
}
