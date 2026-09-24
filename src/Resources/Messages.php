<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\ValidationException;

final class Messages extends BaseResource
{
    public const MAX_BATCH_SIZE = 1000;

    /**
     * POST /v1/messages.
     *
     * O receiver aceita `whatsapp:+5511...`, `+5511...` ou só dígitos; quem valida o número é a API.
     * Toda chamada envia Idempotency-Key (gerada se não informada) e reaproveita a mesma chave nos retries.
     *
     * @param array<int, string> $variables
     * @param array<string, mixed> $extra campos extras do contrato (sender, scheduledAt, interactive, mode, replyTo...)
     * @return array<string, mixed>
     */
    public function send(
        string $receiver,
        ?string $templateName = null,
        array $variables = [],
        ?string $body = null,
        ?string $mediaUrl = null,
        array $extra = [],
        ?string $idempotencyKey = null,
    ): array {
        if (trim($receiver) === '') {
            throw new ValidationException(['message' => 'The receiver field is required.']);
        }

        if ($templateName !== null && trim($templateName) === '') {
            throw new ValidationException(['message' => 'The templateName field is required.']);
        }

        $payload = array_merge($extra, ['receiver' => trim($receiver)]);

        if ($templateName !== null) {
            $payload['templateName'] = $templateName;
            $payload['variables'] = $variables;
        }

        if ($body !== null && $body !== '') {
            $payload['body'] = $body;
        }

        if ($mediaUrl !== null && $mediaUrl !== '') {
            $payload['mediaUrl'] = $mediaUrl;
        }

        return $this->httpPost('messages', $this->withIdempotencyKey(['json' => $payload], $idempotencyKey));
    }

    /**
     * POST /v1/messages/batch (até 1000 mensagens do mesmo template).
     *
     * @param array<int, array<string, mixed>> $messages
     * @return array<string, mixed>
     */
    public function sendBatch(string $templateName, array $messages, ?string $idempotencyKey = null): array
    {
        if (trim($templateName) === '') {
            throw new ValidationException(['message' => 'The templateName field is required.']);
        }

        if ($messages === []) {
            throw new ValidationException(['message' => 'The messages field must not be empty.']);
        }

        if (count($messages) > self::MAX_BATCH_SIZE) {
            throw new ValidationException(['message' => 'A batch accepts at most ' . self::MAX_BATCH_SIZE . ' messages.']);
        }

        return $this->httpPost('messages/batch', $this->withIdempotencyKey([
            'json' => ['templateName' => $templateName, 'messages' => array_values($messages)],
        ], $idempotencyKey));
    }

    /**
     * GET /v1/messages/{id}.
     *
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->httpGet('messages/' . rawurlencode($id));
    }
}
