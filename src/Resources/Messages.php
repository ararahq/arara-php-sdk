<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\ValidationException;

final class Messages extends BaseResource
{
    public const MAX_BATCH_SIZE = 1000;

    /** Campos que têm parâmetro próprio em send() e por isso não podem vir em $extra. */
    private const RESERVED_EXTRA_KEYS = ['receiver', 'templateName', 'templateVariables', 'variables', 'body', 'mediaUrl', 'media_url'];

    /** Tipos de conteúdo do SendMessageRequest: a API exige exatamente um (isValidPayload). */
    private const OBJECT_CONTENT_KEYS = ['interactive', 'location', 'reaction'];

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

        $reserved = array_values(array_intersect(array_keys($extra), self::RESERVED_EXTRA_KEYS));
        if ($reserved !== []) {
            throw new ValidationException(['message' => 'Pass ' . implode(', ', $reserved) . ' as named arguments, not inside extra.']);
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

        self::assertSingleContentType($payload);

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
     * @param array<string, mixed> $payload
     */
    private static function assertSingleContentType(array $payload): void
    {
        $present = [];

        foreach (['templateName', 'body'] as $key) {
            if (is_string($payload[$key] ?? null) && trim($payload[$key]) !== '') {
                $present[] = $key;
            }
        }

        foreach (self::OBJECT_CONTENT_KEYS as $key) {
            if (($payload[$key] ?? null) !== null) {
                $present[] = $key;
            }
        }

        if (count($present) === 1) {
            return;
        }

        $found = $present === [] ? 'none' : implode(', ', $present);

        throw new ValidationException(['message' => "Send exactly one content type: templateName, body, interactive, location or reaction (got {$found})."]);
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
