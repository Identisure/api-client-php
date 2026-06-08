<?php

declare(strict_types=1);

namespace Idkollen\Client;

enum Environment
{
    case Production;
    case Staging;

    public function baseUrl(): string
    {
        return match ($this) {
            self::Production => 'https://api.idkollen.se',
            self::Staging => 'https://stgapi.idkollen.se',
        };
    }
}
