<img align="left" height="100" style="margin-right: 10px;" src="img/icon.png">

# Identisure PHP Client

[![Packagist version][packagist-image]][packagist-url]
[![MIT license][license-image]][license-url]

[packagist-image]: https://img.shields.io/packagist/v/identisure/client?style=flat-square
[packagist-url]: https://packagist.org/packages/identisure/client
[license-image]: https://img.shields.io/badge/license-MIT-blue?style=flat-square
[license-url]: https://opensource.org/license/mit

API client for the [Identisure](https://developers.identisure.com) REST API.

## Installation

```sh
composer require identisure/client
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

use Identisure\Client\Environment;
use Identisure\Client\IdentisureClientBuilder;
use Identisure\Client\Models\BankIdSe\BankIdSeAuthRequest;
use Identisure\Client\Models\BankIdSe\BankIdSeCompleted;
use Identisure\Client\Models\BankIdSe\BankIdSeFailed;
use Identisure\Client\Models\BankIdSe\BankIdSePending;

$client = (new IdentisureClientBuilder('client_id', 'client_secret'))
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

### Verifying callbacks

Pass the raw request body exactly as received, before parsing it.

```php
use Identisure\Client\CallbackSignature;

$valid = CallbackSignature::verify($rawBody, $request->getHeaderLine('X-Signature-SHA256'), $hmacSecret);
```

## Development

### Prerequisites

- PHP 8.3+
- [Composer](https://getcomposer.org/)

The tests use shared fixtures from a git submodule. Clone with
`git clone --recurse-submodules`, or run `git submodule update --init` in an
existing clone.

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
