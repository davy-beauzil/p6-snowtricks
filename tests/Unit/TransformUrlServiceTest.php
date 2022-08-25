<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\TransformUrlService;
use Iterator;
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

    protected function dataProvider_testGetEmbedUrl(): Iterator
    {
        yield ['https://www.youtube.com/watch?v=MVOmvXJtg1Q', 'https://www.youtube.com/embed/MVOmvXJtg1Q'];
        yield ['https://www.youtube.com/watch?v=4vK-y4GXb-c', 'https://www.youtube.com/embed/4vK-y4GXb-c'];
        yield [
            'https://www.dailymotion.com/video/x8cmwj1?playlist=x5nmbq',
            'https://www.dailymotion.com/embed/video/x8cmwj1',
        ];
        yield [
            'https://www.dailymotion.com/video/x8cmw77?playlist=x5nmbq',
            'https://www.dailymotion.com/embed/video/x8cmw77',
        ];
        yield ['https://vimeo.com/33316741', null];
        yield ['', null];
    }
}
