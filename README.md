# Arara PHP SDK

Official PHP SDK for **AraraHQ**. Simple, typed, and developer-first.

## Installation

```bash
composer require arara/arara-php-sdk
```

## Configuration

```php
use Arara\Arara;
use Arara\Config;

$config = new Config(
    apiKey: 'sk_live_...',
);

$sdk = new Arara($config);
```

## Resources

### 1. Messages (`$sdk->messages`)

```php
$response = $sdk->messages->send(
    receiver: 'whatsapp:+5511999999999',
    templateName: 'welcome',
    variables: ['John']
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
