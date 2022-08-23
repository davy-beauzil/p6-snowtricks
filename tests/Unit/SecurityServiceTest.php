<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\SecurityService;
use function mb_strlen;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SecurityServiceTest extends TestCase
{
    /**
     * @test
     * @dataProvider checkStrengthPasswordTest_dataProvider
     */
    public function checkStrengthPasswordTest(string $password, bool $result): void
    {
        static::assertSame(SecurityService::checkStrengthPassword($password), $result);
    }

    /**
     * @test
     */
    public function generateRamdomIdTest(): void
    {
        $id = SecurityService::generateRamdomId();
        static::assertIsString($id);
        static::assertSame(128, mb_strlen($id));
    }
}
