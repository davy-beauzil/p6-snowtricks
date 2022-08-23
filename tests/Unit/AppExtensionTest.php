<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\ScalewayService;
use App\Twig\AppExtension;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AppExtensionTest extends TestCase
{
    private AppExtension $appExtension;

    protected function setUp(): void
    {
        $this->appExtension = new AppExtension(new ScalewayService());
    }

    /**
     * @test
     */
    public function getPublicImage(): void
    {
        $url = $this->appExtension->getPublicImage('tests/public-image.png');
        static::assertSame('https://p6-snowtricks.s3.fr-par.scw.cloud/tests/public-image.png', $url);
    }

    /**
     * @test
     */
    public function getPrivateImage(): void
    {
        $url = $this->appExtension->getPublicImage('tests/private-image.png');
        static::assertSame('https://p6-snowtricks.s3.fr-par.scw.cloud/tests/private-image.png', $url);
    }

    /**
     * @test
     */
    public function getNoImage(): void
    {
        $noImage = $this->appExtension->getNoImage();
        static::assertSame(Uri::class, $noImage::class);
        static::assertSame('p6-snowtricks.s3.fr-par.scw.cloud', $noImage->getHost());
        static::assertSame('/assets/images/no-image.png', $noImage->getPath());
    }
}
