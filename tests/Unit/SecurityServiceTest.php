<?php

namespace App\Tests\Unit;

use App\Services\SecurityService;
use PHPUnit\Framework\TestCase;

class SecurityServiceTest extends TestCase
{
    /**
     * @test
     * @dataProvider checkStrengthPasswordTest_dataProvider
     */
    public function checkStrengthPasswordTest(string $password, bool $result): void
    {
        self::assertSame(SecurityService::checkStrengthPassword($password), $result);
    }

    /**
     * @test
     */
    public function generateRamdomIdTest(): void
    {
        $id = SecurityService::generateRamdomId();
        self::assertIsString($id);
        self::assertSame(128, strlen($id));
    }

    private function checkStrengthPasswordTest_dataProvider(): array
    {
        return [
            [
              'testtest',
              false
            ],
            [
                '12341234',
                false
            ],
            [
                '@-/!@-/!',
                false
            ],
            [
                'test@test@',
                false
            ],
            [
                'test1234',
                false
            ],
            [
                '@-/!1234',
                false
            ],
            [
                't1@',
                false
            ],
            [
                'test@1234',
                true
            ],
        ];
    }

}