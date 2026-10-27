<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\Vipps\VippsAuthRequest;
use Identisure\Client\Models\Vipps\VippsBackchannelAuthRequest;
use Identisure\Client\Models\Vipps\VippsCompleted;
use Identisure\Client\Models\Vipps\VippsFailed;
use Identisure\Client\Models\Vipps\VippsPending;
use Identisure\Client\Models\Vipps\VippsStatus;
use Identisure\Client\Models\Vipps\VippsUnknown;
use Identisure\Client\PollOptions;
use Identisure\Client\Transport;
use Identisure\Client\WaitException;

final class VippsEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(VippsAuthRequest $req): VippsStatus
    {
        return self::parseStatus($this->transport->post('/v3/vipps/auth', self::serialize($req)));
    }

    public function backchannelAuth(VippsBackchannelAuthRequest $req): VippsStatus
    {
        return self::parseStatus($this->transport->post('/v3/vipps/backchannel/auth', self::serialize($req)));
    }

    public function authStatus(string $id): VippsStatus
    {
        return self::parseStatus($this->transport->get('/v3/vipps/auth/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/vipps/auth/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): VippsStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $this->authStatus($id);
            if (!$status instanceof VippsPending) {
                return $status;
            }
            if (hrtime(true) >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
    }

    /** @return array<string, mixed> */
    private static function serialize(object $req): array
    {
        return array_filter(get_object_vars($req), fn($v) => $v !== null);
    }

    /** @param array<mixed> $d */
    private static function parseStatus(array $d): VippsStatus
    {
        return match ($d['status']) {
            'PENDING' => new VippsPending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                url: $d['url'] ?? null,
            ),
            'COMPLETED' => new VippsCompleted(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                ssn: $d['ssn'] ?? null,
                name: $d['name'],
                givenName: $d['givenName'],
                surname: $d['surname'],
                phone: $d['phone'] ?? null,
                email: $d['email'] ?? null,
                address: $d['address'] ?? null,
                birthDate: $d['birthDate'] ?? null,
                pid: $d['pid'] ?? null,
                bankId: $d['bankId'] ?? null,
                authenticatedAt: $d['authenticatedAt'] ?? null,
            ),
            'FAILED' => new VippsFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new VippsUnknown(status: $d['status']),
        };
    }
}
