<?php

declare(strict_types=1);

namespace Identisure\Client;

final class CallbackSignature
{
    /**
     * Verifies the X-Signature-SHA256 header of a callback request.
     *
     * Pass the raw request body exactly as received. Verify before parsing and never re-serialize the JSON.
     */
    public static function verify(
        string $body,
        string $signature,
        #[\SensitiveParameter] string $secret,
    ): bool {
        $expected = base64_encode(hash_hmac('sha256', $body, $secret, true));

        return hash_equals($expected, $signature);
    }
}
