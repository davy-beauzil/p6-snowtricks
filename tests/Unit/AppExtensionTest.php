<?php

namespace App\Tests\Unit;

use App\Services\ScalewayService;
use App\Twig\AppExtension;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;

class AppExtensionTest extends TestCase
{
    private AppExtension $appExtension;

    protected function setUp(): void
    {
        $this->appExtension = new AppExtension(new ScalewayService());
    }

    public function testGetPublicImage(): void
    {
        $url = $this->appExtension->getPublicImage('tests/public-image.png');
        self::assertSame('https://p6-snowtricks.s3.fr-par.scw.cloud/tests/public-image.png', $url);
    }

    public function testGetPrivateImage(): void
    {
        $url = $this->appExtension->getPublicImage('tests/private-image.png');
        self::assertSame('https://p6-snowtricks.s3.fr-par.scw.cloud/tests/private-image.png', $url);
    }

    public function testGetNoImage(): void
    {
        $noImage = $this->appExtension->getNoImage();
        self::assertSame(Uri::class, $noImage::class);
        self::assertSame('p6-snowtricks.s3.fr-par.scw.cloud', $noImage->getHost());
        self::assertSame('/assets/images/no-image.png', $noImage->getPath());
    }
}