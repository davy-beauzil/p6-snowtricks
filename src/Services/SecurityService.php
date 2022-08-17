<?php

declare(strict_types=1);

namespace App\Services;

use function mb_strlen;

class SecurityService
{
    public static function checkStrengthPassword(string $password): bool
    {
        $size = mb_strlen($password);
        $containsSpecialCharacter = false;
        $containsNumber = false;

        foreach (['@', '-', '_', '/', '!'] as $specialCharacter) {
            if (str_contains($password, $specialCharacter)) {
                $containsSpecialCharacter = true;
            }
        }
        foreach (['0', '1', '2', '3,', '4', '5', '6', '7', '8', '9'] as $number) {
            if (str_contains($password, $number)) {
                $containsNumber = true;
            }
        }

        return $size > 8 && $containsSpecialCharacter && $containsNumber;
    }

    public static function generateRamdomId(): string
    {
        return bin2hex(random_bytes(64));
    }
}
