<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\TransformUrlService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TransformUrlServiceTest extends TestCase
{
    private TransformUrlService $transformUrlService;

    protected function setUp(): void
    {
        $this->transformUrlService = new TransformUrlService();
    }

    /**
     * @test
     * @dataProvider dataProvider_testGetEmbedUrl
     */
    public function testGetEmbedUrl(string $url, mixed $result): void
    {
        static::assertSame($result, $this->transformUrlService->getEmbedUrl($url));
    }
}
