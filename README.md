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

## Resources

### 1. Messages (`$sdk->messages`)

```php
// Template standard
$response = $sdk->messages->send(
    receiver: 'whatsapp:+5511987654321',
    templateName: 'welcome',
    variables: ['John']
);

// Template com Mídia (Header de Imagem/PDF)
$response = $sdk->messages->send(
    receiver: 'whatsapp:+5511987654321',
    templateName: 'invoice_ready',
    variables: ['John', 'January'],
    mediaUrl: 'https://your-media.com/invoice.pdf'
);

// Mensagem de Sessão (Texto Livre)
$response = $sdk->messages->send(
    receiver: 'whatsapp:+5511987654321',
    body: 'Olá! Como posso ajudar?'
);
```

### 2. Templates (`$sdk->templates`)

```php
$templates = $sdk->templates->list();

$details = $sdk->templates->get('template-name');

$sdk->templates->create([
    'name' => 'promo_christmas',
    'category' => 'MARKETING',
    'language' => 'pt_BR',
    'body' => 'Hi {{1}}, check our Christmas deals!',
    'samples' => ['John']
]);

$sdk->templates->delete('template-name');
```

### 3. Webhook Events

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

```php
use Arara\Exceptions\AraraException;

try {
    $sdk->messages->send(...);
} catch (AraraException $e) {
    echo "Error {$e->statusCode}: {$e->getMessage()}";
    print_r($e->response);
}
```

## License

MIT
