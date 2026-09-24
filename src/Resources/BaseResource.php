<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\ForbiddenException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\PlanFeatureLockedException;
use Arara\Exceptions\RateLimitException;
use Arara\Exceptions\ValidationException;
use Arara\Support\IdempotencyKey;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

abstract class BaseResource
{
    private const HTTP_BAD_REQUEST = 400;

    private const HTTP_UNAUTHORIZED = 401;

    private const HTTP_NOT_FOUND = 404;

    private const HTTP_UNPROCESSABLE = 422;

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
    protected function httpPatch(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->patch($endpoint, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (RequestException $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function httpPut(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->put($endpoint, $options);

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

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function withIdempotencyKey(array $options, ?string $idempotencyKey): array
    {
        $headers = is_array($options['headers'] ?? null) ? $options['headers'] : [];
        $headers[IdempotencyKey::HEADER] = IdempotencyKey::resolve($idempotencyKey);
        $options['headers'] = $headers;

        return $options;
    }

    private function handleException(RequestException $e): AraraException
    {
        $statusCode = $e->getResponse()?->getStatusCode() ?? InternalServerException::DEFAULT_STATUS;
        $body = json_decode((string) $e->getResponse()?->getBody(), true);
        $body = is_array($body) ? $body : null;
        $retryAfter = $this->parseRetryAfter($e);

        return match (true) {
            $statusCode === self::HTTP_BAD_REQUEST => new BadRequestException($body),
            $statusCode === self::HTTP_UNAUTHORIZED => new AuthenticationException($body),
            $statusCode === ForbiddenException::STATUS => $this->forbidden($body),
            $statusCode === self::HTTP_NOT_FOUND => new NotFoundException($body),
            $statusCode === self::HTTP_UNPROCESSABLE => new ValidationException($body),
            $statusCode === RateLimitException::STATUS => new RateLimitException($body, $retryAfter),
            $statusCode >= InternalServerException::DEFAULT_STATUS => new InternalServerException($body, $statusCode, $retryAfter),
            default => new AraraException($statusCode, $body, null, $retryAfter),
        };
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function forbidden(?array $body): AraraException
    {
        $error = is_array($body['error'] ?? null) ? $body['error'] : [];
        $code = is_string($error['code'] ?? null) ? $error['code'] : null;

        if ($code === null) {
            return new AuthenticationException($body, ForbiddenException::STATUS);
        }

        if ($code === PlanFeatureLockedException::CODE) {
            return new PlanFeatureLockedException($body);
        }

        return new ForbiddenException($body);
    }

    private function parseRetryAfter(RequestException $e): ?int
    {
        $retryAfter = $e->getResponse()?->getHeaderLine('Retry-After');

        return is_numeric($retryAfter) ? (int) $retryAfter : null;
    }
}
