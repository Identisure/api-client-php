<?php

declare(strict_types=1);

namespace Idkollen\Client\Endpoints;

use Idkollen\Client\Models\Freja\FrejaAuthRequest;
use Idkollen\Client\Models\Freja\FrejaBackchannelAuthRequest;
use Idkollen\Client\Models\Freja\FrejaBackchannelSignRequest;
use Idkollen\Client\Models\Freja\FrejaCompleted;
use Idkollen\Client\Models\Freja\FrejaFailed;
use Idkollen\Client\Models\Freja\FrejaPending;
use Idkollen\Client\Models\Freja\FrejaSignRequest;
use Idkollen\Client\Models\Freja\FrejaStatus;
use Idkollen\Client\PollOptions;
use Idkollen\Client\Transport;
use Idkollen\Client\WaitException;

final class FrejaEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(FrejaAuthRequest $req): FrejaStatus
    {
        return self::parseStatus($this->transport->post('/v3/freja/auth', self::serialize($req)));
    }

    public function backchannelAuth(FrejaBackchannelAuthRequest $req): FrejaStatus
    {
        return self::parseStatus($this->transport->post('/v3/freja/backchannel/auth', self::serialize($req)));
    }

    public function sign(FrejaSignRequest $req): FrejaStatus
    {
        return self::parseStatus($this->transport->post('/v3/freja/sign', self::serialize($req)));
    }

    public function backchannelSign(FrejaBackchannelSignRequest $req): FrejaStatus
    {
        return self::parseStatus($this->transport->post('/v3/freja/backchannel/sign', self::serialize($req)));
    }

    public function authStatus(string $id): FrejaStatus
    {
        return self::parseStatus($this->transport->get('/v3/freja/auth/' . $id));
    }

    public function signStatus(string $id): FrejaStatus
    {
        return self::parseStatus($this->transport->get('/v3/freja/sign/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/freja/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/freja/sign/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): FrejaStatus
    {
        return $this->poll(fn() => $this->authStatus($id), $opts);
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): FrejaStatus
    {
        return $this->poll(fn() => $this->signStatus($id), $opts);
    }

    private function poll(\Closure $fn, PollOptions $opts): FrejaStatus
    {
        $deadline = time() + $opts->timeout;
        while (true) {
            $status = $fn();
            if (!$status instanceof FrejaPending) {
                return $status;
            }
            if (time() >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
    }

    private static function serialize(object $req): array
    {
        return array_filter(get_object_vars($req), fn($v) => $v !== null);
    }

    private static function parseStatus(array $d): FrejaStatus
    {
        return match ($d['status']) {
            'PENDING' => new FrejaPending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                autoStartToken: $d['autoStartToken'],
                qrData: $d['qrData'],
            ),
            'COMPLETED' => new FrejaCompleted(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                ssn: $d['ssn'],
                country: $d['country'],
                name: $d['name'],
                givenName: $d['givenName'],
                surname: $d['surname'],
                address: $d['address'] ?? null,
                companySignatoryText: $d['companySignatoryText'] ?? null,
            ),
            'FAILED' => new FrejaFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => throw new \UnexpectedValueException("Unknown freja status: {$d['status']}"),
        };
    }
}
