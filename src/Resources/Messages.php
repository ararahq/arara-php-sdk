<?php

declare(strict_types=1);

namespace Arara\Resources;

use Arara\Exceptions\ValidationException;

final class Messages extends BaseResource
{
    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    public function send(
        string $receiver, 
        ?string $templateName = null, 
        array $variables = [], 
        ?string $body = null, 
        ?string $mediaUrl = null
    ): array {
        if (trim($receiver) === '') {
            throw new ValidationException(['message' => 'The receiver field is required.']);
        }

        if (!preg_match('/^whatsapp:\+\d{8,15}$/', $receiver)) {
            throw new ValidationException(['message' => 'The receiver must follow the format whatsapp:+<number> (e.g. whatsapp:+5511999999999).']);
        }

        $payload = [
            'receiver' => $receiver,
        ];

        if ($templateName) {
            $payload['templateName'] = $templateName;
            $payload['variables'] = $variables;
        }

        if ($body) {
            $payload['body'] = $body;
        }

        if ($mediaUrl) {
            $payload['media_url'] = $mediaUrl;
        }

        return $this->post('messages', [
            'json' => $payload,
        ]);
    }
}
