<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\AgeVerification\AgeVerificationCompleted;
use Identisure\Client\Models\AgeVerification\AgeVerificationFailed;
use Identisure\Client\Models\AgeVerification\AgeVerificationPending;
use Identisure\Client\Models\AgeVerification\AgeVerificationRequest;
use Identisure\Client\Models\AgeVerification\AgeVerificationStatus;
use Identisure\Client\Models\AgeVerification\AgeVerificationUnknown;
use Identisure\Client\Models\Ftn\FtnAuthRequest;
use Identisure\Client\Models\Ftn\FtnCompleted;
use Identisure\Client\Models\Ftn\FtnFailed;
use Identisure\Client\Models\Ftn\FtnPending;
use Identisure\Client\Models\Ftn\FtnStatus;
use Identisure\Client\Models\Ftn\FtnUnknown;
use Identisure\Client\PollOptions;
use Identisure\Client\Transport;
use Identisure\Client\WaitException;

final class FtnEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(FtnAuthRequest $req): FtnStatus
    {
        return self::parseStatus($this->transport->post('/v3/ftn/auth', self::serialize($req)));
    }

    public function ageVerification(AgeVerificationRequest $req): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->post('/v3/ftn/age-verification', self::serialize($req)));
    }

    public function authStatus(string $id): FtnStatus
    {
        return self::parseStatus($this->transport->get('/v3/ftn/auth/' . $id));
    }

    public function ageVerificationStatus(string $id): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->get('/v3/ftn/age-verification/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/ftn/auth/' . $id);
    }

    public function cancelAgeVerification(string $id): void
    {
        $this->transport->delete('/v3/ftn/age-verification/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): FtnStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $this->authStatus($id);
            if (!$status instanceof FtnPending) {
                return $status;
            }
            if (hrtime(true) >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
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

    private static function serialize(object $req): array
    {
        return array_filter(get_object_vars($req), fn($v) => $v !== null);
    }

    private static function parseStatus(array $d): FtnStatus
    {
        return match ($d['status']) {
            'PENDING' => new FtnPending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                url: $d['url'],
            ),
            'COMPLETED' => new FtnCompleted(
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
                bankName: $d['bankName'] ?? null,
                authenticatedAt: $d['authenticatedAt'] ?? null,
            ),
            'FAILED' => new FtnFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new FtnUnknown(status: $d['status']),
        };
    }

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
}
