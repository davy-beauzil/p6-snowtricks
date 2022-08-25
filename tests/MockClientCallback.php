<?php

declare(strict_types=1);

namespace App\Tests;

use function array_key_exists;
use LogicException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockClientCallback
{
    /**
     * @var array<string, ResponseInterface>
     */
    private array $responses = [];

    /**
     * @var array<string, int>
     */
    private array $called = [];

    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        $key = sprintf('%s-%s', $method, $url);
        if (! array_key_exists($key, $this->called)) {
            $this->called[$key] = 0;
        }
        ++$this->called[$key];
        if (! array_key_exists($key, $this->responses)) {
            dump($key);
            throw new LogicException('No response to answer');
        }

        return $this->responses[$key];
    }

    public function setResponse(string $method, string $url, ResponseInterface $response): self
    {
        $this->responses[sprintf('%s-%s', $method, $url)] = $response;

        return $this;
    }

    public function hasBeenCalled(string $method, string $url): int
    {
        $key = sprintf('%s-%s', $method, $url);

        return $this->called[$key] ?? 0;
    }
}
