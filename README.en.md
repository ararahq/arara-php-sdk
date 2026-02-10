# Arara PHP SDK

PHP SDK for integrating with the [Arara](https://ararahq.com) API.

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> **[Leia em Português](README.md)**

## Current SDK scope

At the current state, the SDK exposes:

- `Arara\Config` for auth and transport settings.
- `Arara\Arara` as the main client.
- `Arara::sendMessage()` to send messages through `POST /messages`.

## Requirements

- PHP 8.4+
- JSON extension
- Composer

## Installation

```bash
composer require arara/arara-php-sdk
```

## Quick start

### Create config and client

```php
use Arara\Arara;
use Arara\Config;

$config = new Config(
    apiKey: 'your-api-key',
);

$arara = new Arara($config);
```

### Send message

```php
$response = $arara->sendMessage(
    receiver: '5511999999999',
    templateName: 'order_confirmation',
    variables: [
        'orderId' => '12345',
        'amount' => '$199.90',
    ],
);
```

`sendMessage()` returns an `array` with the API JSON response.

### Available configuration

```php
use Arara\Config;

$config = new Config(
    apiKey: 'your-api-key',
    baseUrl: 'https://api.ararahq.com/api/v1',
    timeout: 30,
    retryTimes: 3,
    retryDelayMs: 100,
);
```

`Config` parameters:

- `apiKey` (string): Bearer auth token.
- `baseUrl` (string): API base URL.
- `timeout` (int): request timeout in seconds.
- `retryTimes` (int): retry count (defined in config, not yet automatically applied by the client).
- `retryDelayMs` (int): retry delay in milliseconds (defined in config, not yet automatically applied by the client).

### Inject custom HTTP client (optional)

```php
use Arara\Arara;
use Arara\Config;
use GuzzleHttp\Client;

$config = new Config(apiKey: 'your-api-key');
$http = new Client();

$arara = new Arara($config, $http);
```

When a custom `Client` is injected, it is used directly.

## Used endpoint

- `POST /messages`

Payload sent by `sendMessage()`:

```json
{
  "receiver": "5511999999999",
  "templateName": "order_confirmation",
  "variables": {}
}
```

## Development

```bash
composer install
composer test
composer analyse
composer format
composer check
```

## License

MIT. See [LICENSE](LICENSE).
