<?php

declare(strict_types=1);

namespace App\Twig;

use App\Services\ScalewayService;
use App\Services\TransformUrlService;
use Psr\Http\Message\UriInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public const NO_IMAGE_PATH = 'assets/images/no-image.png';

    public function __construct(
        private ScalewayService $scalewayService,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_public_image', [$this, 'getPublicImage']),
            new TwigFunction('get_private_image', [$this, 'getPrivateImage']),
            new TwigFunction('get_no_image', [$this, 'getNoImage']),
            new TwigFunction('get_embed_url', [$this, 'getEmbedUrl']),
        ];
    }

    public function getPublicImage(string $path): string
    {
        return $this->scalewayService->getUrlForPublicFile($path);
    }

    public function getPrivateImage(string $path): UriInterface
    {
        return $this->scalewayService->getUrlForPrivateFile($path);
    }

    public function getNoImage(): UriInterface
    {
        return $this->scalewayService->getUrlForPrivateFile(self::NO_IMAGE_PATH);
    }

    public function getEmbedUrl(string $url): ?string
    {
        return TransformUrlService::getEmbedUrl($url);
    }

    public function getScalewayService(): ScalewayService
    {
        return $this->scalewayService;
    }
}
