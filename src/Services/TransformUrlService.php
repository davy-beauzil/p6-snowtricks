<?php

declare(strict_types=1);

namespace App\Services;

use function array_key_exists;
use Exception;
use const PHP_URL_PATH;

class TransformUrlService
{
    public const YOUTUBE_BASE_URL = ['youtube.com/watch?v=', 'youtu.be/'];

    public const YOUTUBE_BASE_EMBED = 'https://www.youtube.com/embed/';

    public const DAYLIMOTION_BASE_URL = ['dailymotion.com/video/', 'dai.ly/'];

    public const DAYLIMOTION_BASE_EMBED = 'https://www.dailymotion.com/embed/video/';

    public static function getEmbedUrl(string $url): ?string
    {
        try {
            foreach (self::YOUTUBE_BASE_URL as $item) {
                if (str_contains($url, $item)) {
                    $code = self::getYoutubeCode($url);

                    return self::YOUTUBE_BASE_EMBED . $code;
                }
            }
            foreach (self::DAYLIMOTION_BASE_URL as $item) {
                if (str_contains($url, $item)) {
                    $code = self::getDaylimotionCode($url);

                    return self::DAYLIMOTION_BASE_EMBED . $code;
                }
            }
            throw new Exception();
        } catch (Exception) {
            return null;
        }
    }

    public static function isValid(string $url): bool
    {
        return self::getEmbedUrl($url) !== null;
    }

    /**
     * from https://www.youtube.com/watch?v=OUKd0e2ph1Y ou https://youtu.be/OUKd0e2ph1Y to
     * https://www.youtube.com/embed/OUKd0e2ph1Y.
     */
    private static function getYoutubeCode(string $url): string
    {
        /** @var array $params */
        $params = parse_url($url);

        if (array_key_exists('query', $params)) {
            $query = null;
            parse_str($params['query'], $query);
            if (array_key_exists('v', $query)) {
                return $query['v'];
            }
        }

        if (array_key_exists('path', $params)) {
            return str_replace('/', '', $params['path']);
        }

        throw new Exception(sprintf('Impossible de récupérer le code pour cette url : %s', $url));
    }

    /**
     * from https://www.dailymotion.com/video/x8bugx4?playlist=x5nmbq ou https://dai.ly/x8bugx4 to
     * https://www.dailymotion.com/embed/video/x8bugx4.
     */
    private static function getDaylimotionCode(string $url): string
    {
        /** @var string $path */
        $path = parse_url($url, PHP_URL_PATH);
        if (str_contains($url, 'dailymotion.com')) {
            return str_replace('/video/', '', $path);
        }
        if (str_contains($url, 'dai.ly')) {
            return str_replace('/', '', $path);
        }
        throw new Exception(sprintf('Impossible de récupérer le code pour cette url : %s', $url));
    }
}
