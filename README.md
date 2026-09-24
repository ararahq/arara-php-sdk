# Arara PHP SDK

[![Packagist](https://img.shields.io/packagist/v/ararahq/sdk)](https://packagist.org/packages/ararahq/sdk)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![Docs](https://img.shields.io/badge/Docs-docs.ararahq.com-orange)](https://docs.ararahq.com)

Official PHP SDK for **[AraraHQ](https://ararahq.com)** — the developer-first WhatsApp API. Simple, typed, and developer-first.

## Installation

```bash
composer require ararahq/sdk
```

## Configuration

```php
use Arara\Arara;
use Arara\Config;

$config = new Config(
    apiKey: 'ara_live_...',
);

$sdk = new Arara($config);
```

## Permissões da chave

Chave com permissão `READ` só lê messages, campaigns, templates, numbers, automations, flows e charges. **`contacts`, `conversations`, `wallet`, `smartLinks`, `optOuts` (leitura) e `auth()->me()` exigem chave `ADMIN`.** Sem permissão, a API responde 403 e o SDK lança `AuthenticationException` (statusCode 403).

## Resources

### 1. Messages (`$sdk->messages`)

O `receiver` aceita `whatsapp:+5511987654321`, `+5511987654321` ou `5511987654321`.

Toda chamada de envio manda `Idempotency-Key`. Se você não passar uma, o SDK gera um UUID e reaproveita a mesma chave em todos os retries daquela chamada, então um timeout seguido de retry nunca duplica o envio. Passe a sua (`idempotencyKey: 'pedido-42'`) para deduplicar entre execuções do seu sistema.

```php
// Template
$response = $sdk->messages->send(
    receiver: '+5511987654321',
    templateName: 'welcome',
    variables: ['John'],
    idempotencyKey: 'pedido-42',
);

// Template com mídia no header
$response = $sdk->messages->send(
    receiver: '5511987654321',
    templateName: 'invoice_ready',
    variables: ['John', 'January'],
    mediaUrl: 'https://your-media.com/invoice.pdf',
);

// Texto livre (janela de 24h) com campos extras do contrato
$response = $sdk->messages->send(
    receiver: 'whatsapp:+5511987654321',
    body: 'Olá! Como posso ajudar?',
    extra: ['sender' => '+5511900000000'],
);

// Consultar uma mensagem
$message = $sdk->messages->get($response['id']);

// Lote (até 1000 mensagens do mesmo template)
$batch = $sdk->messages->sendBatch('welcome', [
    ['receiver' => '+5511987654321', 'variables' => ['Ana']],
    ['receiver' => '+5521987654321', 'variables' => ['Bruno']],
]);
```

### 2. Templates (`$sdk->templates`)

Templates são endereçados pelo **id** (UUID), não pelo nome.

```php
$page = $sdk->templates->list(page: 0, size: 50, status: 'APPROVED');
foreach ($page->data as $template) {
    echo $template['id'], ' ', $template['name'], PHP_EOL;
}
$page->pagination->totalPages;
$page->hasNextPage();

// Buscar pelo nome: filtre a lista
$welcome = $sdk->templates->list(name: 'welcome')->data[0] ?? null;

$details = $sdk->templates->get($welcome['id']);
$status = $sdk->templates->getStatus($welcome['id']);
$stats = $sdk->templates->analytics($welcome['id'], '7d');

$sdk->templates->create([
    'name' => 'promo_christmas',
    'category' => 'MARKETING',
    'language' => 'pt_BR',
    'body' => 'Hi {{1}}, check our Christmas deals!',
    'samples' => ['John'],
]);

$sdk->templates->delete($welcome['id']);
```

### 3. Outros recursos

```php
$sdk->campaigns->create([...]);           // Idempotency-Key automático
$sdk->smartLinks->list(page: 0, size: 50); // PaginatedResponse
$sdk->optOuts->add('+5511987654321', 'pediu para sair');
$sdk->optOuts->remove('+5511987654321');
$me = $sdk->auth()->me();                  // GET /auth/me (chave ADMIN)
```

### 4. Webhook Events

```php
use Arara\Utils\WebhookUtils;

$payload = file_get_contents('php://input');

// Valide a assinatura antes de processar. O payload precisa ser o corpo cru.
$isValid = WebhookUtils::verifySignature(
    payload: $payload,
    signature: $_SERVER['HTTP_X_ARARA_SIGNATURE'] ?? '',
    secret: getenv('ARARA_WEBHOOK_SECRET'),
    timestamp: $_SERVER['HTTP_X_ARARA_TIMESTAMP'] ?? null,
);

if (!$isValid) {
    http_response_code(401);
    exit;
}

$data = json_decode($payload, true);

if (WebhookUtils::isMessageStatusEvent($data)) {
    $status = $data['data']['status'];
    // Handle status update
}

if (WebhookUtils::isInboundMessageEvent($data)) {
    $from = $data['data']['from'];
    $body = $data['data']['body'];
    // Handle inbound message
}
```

## Error Handling

Toda falha HTTP vira uma `Arara\Exceptions\AraraException` com `statusCode`, `errorCode`, `getMessage()`, `details` e `retryAfter`.

| Situação | Exceção |
|---|---|
| 400 | `BadRequestException` |
| 401, ou 403 sem código (chave inválida/sem permissão) | `AuthenticationException` |
| 403 `PLAN_FEATURE_LOCKED` | `PlanFeatureLockedException` (`feature`, `currentPlan`, `upgradeTo`) |
| outro 403 com código | `ForbiddenException` |
| 404 | `NotFoundException` |
| 422 | `ValidationException` |
| 429 | `RateLimitException` (`retryAfter`) |
| 5xx | `InternalServerException` |

```php
use Arara\Exceptions\AraraException;
use Arara\Exceptions\PlanFeatureLockedException;

try {
    $sdk->messages->send('+5511987654321', 'welcome');
} catch (PlanFeatureLockedException $e) {
    echo "Disponível a partir do plano {$e->upgradeTo}";
} catch (AraraException $e) {
    echo "Error {$e->statusCode} {$e->errorCode}: {$e->getMessage()}";
}
```

## Migrando da 1.x

Veja o [CHANGELOG](CHANGELOG.md): `users`, `organizations` e `apiKeys` saíram; templates usam id; listas de templates e smart links são paginadas.

## License

MIT
