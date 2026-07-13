<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\RateLimitException;
use Arara\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

abstract class BaseResource
{
    public function __construct(
        protected readonly Client $client,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function httpPost(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->post($endpoint, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (RequestException $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function httpGet(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->get($endpoint, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (RequestException $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function httpDelete(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->delete($endpoint, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (RequestException $e) {
            throw $this->handleException($e);
        }
    }

    private function handleException(RequestException $e): AraraException
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? 500;
        $body = json_decode((string) $e->getResponse()?->getBody(), true);
        $body = is_array($body) ? $body : null;

        return match ($statusCode) {
            400 => new BadRequestException($body),
            401 => new AuthenticationException($body),
            404 => new NotFoundException($body),
            422 => new ValidationException($body),
            429 => new RateLimitException($body, $this->parseRetryAfter($e)),
            500 => new InternalServerException($body),
            default => new AraraException($statusCode, $body),
        };
    }

    private function parseRetryAfter(RequestException $e): ?int
    {
        $retryAfter = $e->getResponse()?->getHeaderLine('Retry-After');

        return is_numeric($retryAfter) ? (int) $retryAfter : null;
    }
}
