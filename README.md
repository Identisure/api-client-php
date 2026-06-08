<img align="left" height="100" style="margin-right: 10px;" src="../img/icon.png">

# IDkollen PHP Client

[![Packagist version][packagist-image]][packagist-url]
[![MIT license][license-image]][license-url]

[packagist-image]: https://img.shields.io/packagist/v/idkollen/client?style=flat-square
[packagist-url]: https://packagist.org/packages/idkollen/client
[license-image]: https://img.shields.io/badge/license-MIT-blue?style=flat-square
[license-url]: https://opensource.org/license/mit

API client for the [IDkollen](https://developers.idkollen.se) REST API.

## Installation

```sh
composer require idkollen/client
```

A PSR-18 HTTP client is required. The client auto-discovers
[Guzzle](https://github.com/guzzle/guzzle) if installed:

```sh
composer require guzzlehttp/guzzle
```

Alternatively, inject any PSR-18 client via the builder.

## Usage

```php
<?php

use Idkollen\Client\Environment;
use Idkollen\Client\IdkollenClientBuilder;
use Idkollen\Client\Models\BankIdSe\BankIdSeAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeCompleted;
use Idkollen\Client\Models\BankIdSe\BankIdSeFailed;
use Idkollen\Client\Models\BankIdSe\BankIdSePending;

$client = (new IdkollenClientBuilder('client_id', 'client_secret'))
    ->environment(Environment::Staging)
    ->build();

$session = $client->bankIdSe()->auth(new BankIdSeAuthRequest());
assert($session instanceof BankIdSePending);
echo "Session ID: {$session->id}\n";

$result = $client->bankIdSe()->waitForAuth($session->id);

if ($result instanceof BankIdSeCompleted) {
    echo "Authenticated: {$result->name} ({$result->ssn})\n";
} elseif ($result instanceof BankIdSeFailed) {
    echo "Failed: {$result->error}\n";
}
```

## Development

### Prerequisites

- PHP 8.3+
- [Composer](https://getcomposer.org/)

### Build

```sh
composer install
```

### Test

```sh
composer test
```

### Check

```sh
composer check
```
