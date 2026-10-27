<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\AgeVerification\AgeVerificationCompleted;
use Identisure\Client\Models\AgeVerification\AgeVerificationFailed;
use Identisure\Client\Models\AgeVerification\AgeVerificationPending;
use Identisure\Client\Models\AgeVerification\AgeVerificationRequest;
use Identisure\Client\Models\AgeVerification\AgeVerificationStatus;
use Identisure\Client\Models\AgeVerification\AgeVerificationUnknown;
use Identisure\Client\Models\Freja\FrejaAuthRequest;
use Identisure\Client\Models\Freja\FrejaBackchannelAuthRequest;
use Identisure\Client\Models\Freja\FrejaBackchannelSignRequest;
use Identisure\Client\Models\Freja\FrejaCompleted;
use Identisure\Client\Models\Freja\FrejaFailed;
use Identisure\Client\Models\Freja\FrejaPending;
use Identisure\Client\Models\Freja\FrejaSignRequest;
use Identisure\Client\Models\Freja\FrejaStatus;
use Identisure\Client\Models\Freja\FrejaUnknown;
use Identisure\Client\PollOptions;
use Identisure\Client\Transport;
use Identisure\Client\WaitException;

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

    public function ageVerification(AgeVerificationRequest $req): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->post('/v3/freja/age-verification', self::serialize($req)));
    }

    public function authStatus(string $id): FrejaStatus
    {
        return self::parseStatus($this->transport->get('/v3/freja/auth/' . $id));
    }

    public function signStatus(string $id): FrejaStatus
    {
        return self::parseStatus($this->transport->get('/v3/freja/sign/' . $id));
    }

    public function ageVerificationStatus(string $id): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->get('/v3/freja/age-verification/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/freja/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/freja/sign/' . $id);
    }

    public function cancelAgeVerification(string $id): void
    {
        $this->transport->delete('/v3/freja/age-verification/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): FrejaStatus
    {
        return $this->poll(fn() => $this->authStatus($id), $opts);
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): FrejaStatus
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

    private function poll(\Closure $fn, PollOptions $opts): FrejaStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $fn();
            if (!$status instanceof FrejaPending) {
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
                authenticatedAt: $d['authenticatedAt'] ?? null,
                signedAt: $d['signedAt'] ?? null,
            ),
            'FAILED' => new FrejaFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new FrejaUnknown(status: $d['status']),
        };
    }
}
