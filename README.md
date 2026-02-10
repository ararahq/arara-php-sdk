# Arara PHP SDK

SDK PHP para integração com a API da [Arara](https://ararahq.com).

[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> **[Read in English](README.en.md)**

## Estado atual da SDK

No estado atual, a SDK expõe:

- `Arara\Config` para configurar autenticação e transporte.
- `Arara\Arara` como cliente principal.
- `Arara::sendMessage()` para envio de mensagens via `POST /messages`.

## Requisitos

- PHP 8.4+
- Extensão JSON
- Composer

## Instalação

```bash
composer require arara/arara-php-sdk
```

## Uso rápido

### Criar configuração e cliente

```php
use Arara\Arara;
use Arara\Config;

$config = new Config(
    apiKey: 'sua-api-key',
);

$arara = new Arara($config);
```

### Enviar mensagem

```php
$response = $arara->sendMessage(
    receiver: '5511999999999',
    templateName: 'order_confirmation',
    variables: [
        'orderId' => '12345',
        'amount' => 'R$ 199,90',
    ],
);
```

`sendMessage()` retorna um `array` com o JSON de resposta da API.

### Configurações disponíveis

```php
use Arara\Config;

$config = new Config(
    apiKey: 'sua-api-key',
    baseUrl: 'https://api.ararahq.com/api/v1',
    timeout: 30,
    retryTimes: 3,
    retryDelayMs: 100,
);
```

Parâmetros de `Config`:

- `apiKey` (string): token de autenticação Bearer.
- `baseUrl` (string): URL base da API.
- `timeout` (int): timeout de requisição em segundos.
- `retryTimes` (int): tentativas de retry (definido na configuração, ainda não aplicado automaticamente no cliente).
- `retryDelayMs` (int): delay entre retries em ms (definido na configuração, ainda não aplicado automaticamente no cliente).

### Injetar cliente HTTP customizado (opcional)

```php
use Arara\Arara;
use Arara\Config;
use GuzzleHttp\Client;

$config = new Config(apiKey: 'sua-api-key');
$http = new Client();

$arara = new Arara($config, $http);
```

Quando um `Client` customizado é injetado, ele é usado diretamente.

## Endpoint utilizado

- `POST /messages`

Payload enviado por `sendMessage()`:

```json
{
  "receiver": "5511999999999",
  "templateName": "order_confirmation",
  "variables": {}
}
```

## Desenvolvimento

```bash
composer install
composer test
composer analyse
composer format
composer check
```

## Licença

MIT. Veja [LICENSE](LICENSE).
