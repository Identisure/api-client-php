<?php

declare(strict_types=1);

namespace Idkollen\Client\Endpoints;

use Idkollen\Client\Models\MitId\MitIdAuthRequest;
use Idkollen\Client\Models\MitId\MitIdBackchannelAuthRequest;
use Idkollen\Client\Models\MitId\MitIdCompleted;
use Idkollen\Client\Models\MitId\MitIdFailed;
use Idkollen\Client\Models\MitId\MitIdPending;
use Idkollen\Client\Models\MitId\MitIdSignRequest;
use Idkollen\Client\Models\MitId\MitIdSignResult;
use Idkollen\Client\Models\MitId\MitIdStatus;
use Idkollen\Client\PollOptions;
use Idkollen\Client\Transport;
use Idkollen\Client\WaitException;

final class MitIdEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(MitIdAuthRequest $req): MitIdStatus
    {
        return self::parseStatus($this->transport->post('/v3/mitid/auth', self::serialize($req)));
    }

    public function backchannelAuth(MitIdBackchannelAuthRequest $req): MitIdStatus
    {
        return self::parseStatus($this->transport->post('/v3/mitid/backchannel/auth', self::serialize($req)));
    }

    public function sign(MitIdSignRequest $req): MitIdStatus
    {
        return self::parseStatus($this->transport->post('/v3/mitid/sign', self::serialize($req)));
    }

    public function authStatus(string $id): MitIdStatus
    {
        return self::parseStatus($this->transport->get('/v3/mitid/auth/' . $id));
    }

    public function signStatus(string $id): MitIdStatus
    {
        return self::parseStatus($this->transport->get('/v3/mitid/sign/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/mitid/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/mitid/sign/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): MitIdStatus
    {
        return $this->poll(fn() => $this->authStatus($id), $opts);
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): MitIdStatus
    {
        return $this->poll(fn() => $this->signStatus($id), $opts);
    }

    private function poll(\Closure $fn, PollOptions $opts): MitIdStatus
    {
        $deadline = time() + $opts->timeout;
        while (true) {
            $status = $fn();
            if (!$status instanceof MitIdPending) {
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

    private static function parseStatus(array $d): MitIdStatus
    {
        return match ($d['status']) {
            'PENDING' => new MitIdPending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                url: $d['url'] ?? null,
                bindingMessage: $d['bindingMessage'] ?? null,
            ),
            'COMPLETED' => new MitIdCompleted(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                ssn: $d['ssn'],
                name: $d['name'],
                givenName: $d['givenName'],
                surname: $d['surname'],
                phone: $d['phone'] ?? null,
                email: $d['email'] ?? null,
                address: $d['address'] ?? null,
                birthDate: $d['birthDate'] ?? null,
                pid: $d['pid'] ?? null,
                bankId: $d['bankId'] ?? null,
                signResult: isset($d['signResult'])
                    ? new MitIdSignResult(checksum: $d['signResult']['checksum'])
                    : null,
            ),
            'FAILED' => new MitIdFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => throw new \UnexpectedValueException("Unknown mitid status: {$d['status']}"),
        };
    }
}
