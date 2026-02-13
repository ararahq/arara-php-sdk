<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

abstract class BaseResource
{
    public function __construct(
        protected readonly Client $client
    ) {}

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function post(string $endpoint, array $options = []): array
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
    protected function get(string $endpoint, array $options = []): array
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
    protected function delete(string $endpoint, array $options = []): array
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

        return match ($statusCode) {
            400 => new BadRequestException($body),
            401 => new AuthenticationException($body),
            404 => new NotFoundException($body),
            422 => new ValidationException($body),
            500 => new InternalServerException($body),
            default => new AraraException($statusCode, $body),
        };
    }
}
