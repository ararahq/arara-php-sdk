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
    public function send(string $receiver, string $templateName, array $variables = []): array
    {
        if (trim($receiver) === '') {
            throw new ValidationException(['message' => 'The receiver field is required.']);
        }

        if (!preg_match('/^whatsapp:\+\d{8,15}$/', $receiver)) {
            throw new ValidationException(['message' => 'The receiver must follow the format whatsapp:+<number> (e.g. whatsapp:+5511999999999).']);
        }

        if (trim($templateName) === '') {
            throw new ValidationException(['message' => 'The templateName field is required.']);
        }

        return $this->post('messages', [
            'json' => [
                'receiver' => $receiver,
                'templateName' => $templateName,
                'variables' => $variables,
            ],
        ]);
    }
}
