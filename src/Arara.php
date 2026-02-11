<?php

declare(strict_types=1);

namespace Arara;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Cliente principal do SDK Arara para integração com a API de WhatsApp.
 */
final class Arara
{
    private readonly Config $config;

    private readonly Client $client;

    /**
     * @param Config      $config Configuração de autenticação e transporte.
     * @param Client|null $http   Cliente Guzzle customizado (opcional).
     */
    public function __construct(Config $config, ?Client $http = null,)
    {
        $this->config = $config;
        $this->client = $http ?? new Client([
            'base_uri' => $config->baseUrl . '/api/' . $config->apiVersion.'/',
            'timeout' => $config->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $config->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Envia uma mensagem via WhatsApp.
     *
     * @param string               $receiver     Destinatário no formato whatsapp:+<número>.
     * @param string               $templateName Nome do template de mensagem.
     * @param array<string, mixed> $variables    Variáveis do template.
     *
     * @return array<string, mixed> Resposta decodificada da API.
     *
     * @throws ValidationException Quando os parâmetros são inválidos.
     * @throws AraraException      Quando a API retorna erro.
     */
    public function sendMessage(string $receiver, string $templateName, array $variables = []): array
    {
        if (trim($receiver) === '') {
            throw new ValidationException(message: 'O campo receiver é obrigatório.');
        }

        if (!preg_match('/^whatsapp:\+\d{8,15}$/', $receiver)) {
            throw new ValidationException(message: 'O receiver deve seguir o formato whatsapp:+<número> (ex: whatsapp:+5511999999999).');
        }

        if (trim($templateName) === '') {
            throw new ValidationException(message: 'O campo templateName é obrigatório.');
        }

        try {
            $response = $this->client->post('messages', [
                'json' => [
                    'receiver' => $receiver,
                    'templateName' => $templateName,
                    'variables' => $variables,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
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
