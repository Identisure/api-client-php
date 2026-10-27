<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\AgeVerification\AgeVerificationCompleted;
use Identisure\Client\Models\AgeVerification\AgeVerificationFailed;
use Identisure\Client\Models\AgeVerification\AgeVerificationPending;
use Identisure\Client\Models\AgeVerification\AgeVerificationRequest;
use Identisure\Client\Models\AgeVerification\AgeVerificationStatus;
use Identisure\Client\Models\AgeVerification\AgeVerificationUnknown;
use Identisure\Client\Models\MitId\MitIdAuthRequest;
use Identisure\Client\Models\MitId\MitIdBackchannelAuthRequest;
use Identisure\Client\Models\MitId\MitIdCompleted;
use Identisure\Client\Models\MitId\MitIdFailed;
use Identisure\Client\Models\MitId\MitIdPending;
use Identisure\Client\Models\MitId\MitIdSignRequest;
use Identisure\Client\Models\MitId\MitIdSignResult;
use Identisure\Client\Models\MitId\MitIdStatus;
use Identisure\Client\Models\MitId\MitIdUnknown;
use Identisure\Client\PollOptions;
use Identisure\Client\Transport;
use Identisure\Client\WaitException;

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

    public function ageVerification(AgeVerificationRequest $req): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->post('/v3/mitid/age-verification', self::serialize($req)));
    }

    public function authStatus(string $id): MitIdStatus
    {
        return self::parseStatus($this->transport->get('/v3/mitid/auth/' . $id));
    }

    public function signStatus(string $id): MitIdStatus
    {
        return self::parseStatus($this->transport->get('/v3/mitid/sign/' . $id));
    }

    public function ageVerificationStatus(string $id): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->get('/v3/mitid/age-verification/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/mitid/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/mitid/sign/' . $id);
    }

    public function cancelAgeVerification(string $id): void
    {
        $this->transport->delete('/v3/mitid/age-verification/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): MitIdStatus
    {
        return $this->poll(fn() => $this->authStatus($id), $opts);
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): MitIdStatus
    {
        return $this->poll(fn() => $this->signStatus($id), $opts);
    }

    public function waitForAgeVerification(string $id, PollOptions $opts = new PollOptions()): AgeVerificationStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $this->ageVerificationStatus($id);
            if (!$status instanceof AgeVerificationPending) {
                return $status;
            }
            if (hrtime(true) >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
    }

    private function poll(\Closure $fn, PollOptions $opts): MitIdStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $fn();
            if (!$status instanceof MitIdPending) {
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
    private static function parseAgeStatus(array $d): AgeVerificationStatus
    {
        return match ($d['status']) {
            'PENDING' => new AgeVerificationPending(
                id: $d['id'],
                url: $d['url'] ?? null,
                minAge: $d['minAge'] ?? null,
                maxAge: $d['maxAge'] ?? null,
            ),
            'COMPLETED' => new AgeVerificationCompleted(
                id: $d['id'],
                ageVerified: $d['ageVerified'],
            ),
            'FAILED' => new AgeVerificationFailed(
                id: $d['id'],
                error: $d['error'],
            ),
            default => new AgeVerificationUnknown(status: $d['status']),
        };
    }

    /** @param array<mixed> $d */
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
                signResult: isset($d['signResult'])
                    ? new MitIdSignResult(checksum: $d['signResult']['checksum'])
                    : null,
                authenticatedAt: $d['authenticatedAt'] ?? null,
                signedAt: $d['signedAt'] ?? null,
            ),
            'FAILED' => new MitIdFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new MitIdUnknown(status: $d['status']),
        };
    }
}
