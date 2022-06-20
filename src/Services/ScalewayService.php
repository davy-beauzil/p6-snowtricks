<?php

declare(strict_types=1);

namespace App\Services;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Visibility;
use Psr\Http\Message\UriInterface;

class ScalewayService
{
    public const BUCKET = 'p6-snowtricks';

    public const ENDPOINT = 'https://s3.fr-par.scw.cloud';

    private Filesystem $filesystem;

    private S3Client $client;

    private AwsS3V3Adapter $adapter;

    public function __construct()
    {
        $this->client = new S3Client([
            'endpoint' => self::ENDPOINT,
            'credentials' => [
                'key' => $_ENV['SCALEWAY_KEY'],
                'secret' => $_ENV['SCALEWAY_SECRET'],
            ],
            'region' => 'fr-par',
            'version' => 'latest',
        ]);
        $this->adapter = new AwsS3V3Adapter($this->client, self::BUCKET);
        $this->filesystem = new Filesystem($this->adapter);
    }

    public function getUrlForPublicFile(string $path): string
    {
        return $this->client->getObjectUrl(self::BUCKET, $path);
    }

    public function getUrlForPrivateFile(string $path): UriInterface
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => self::BUCKET,
            'Key' => $path,
        ]);

        return $this->client->createPresignedRequest($command, '+1minutes')
            ->getUri()
        ;
    }

    public function uploadImage(string $path, mixed $stream, bool $isPublic = true): bool
    {
        try {
            $this->filesystem->writeStream($path, $stream, [
                'visibility' => Visibility::PUBLIC,
            ]);

            return true;
        } catch (FilesystemException) {
            return false;
        }
    }

    public function removeFile(string $path): bool
    {
        try {
            $this->filesystem->delete($path);

            return true;
        } catch (FilesystemException) {
            return false;
        }
    }
}
