<?php

declare(strict_types=1);

namespace Identisure\Client\Endpoints;

use Identisure\Client\Models\AgeVerification\AgeVerificationCompleted;
use Identisure\Client\Models\AgeVerification\AgeVerificationFailed;
use Identisure\Client\Models\AgeVerification\AgeVerificationPending;
use Identisure\Client\Models\AgeVerification\AgeVerificationRequest;
use Identisure\Client\Models\AgeVerification\AgeVerificationStatus;
use Identisure\Client\Models\AgeVerification\AgeVerificationUnknown;
use Identisure\Client\Models\BankIdNo\BankIdNoAuthRequest;
use Identisure\Client\Models\BankIdNo\BankIdNoBackchannelAuthRequest;
use Identisure\Client\Models\BankIdNo\BankIdNoCompleted;
use Identisure\Client\Models\BankIdNo\BankIdNoFailed;
use Identisure\Client\Models\BankIdNo\BankIdNoPending;
use Identisure\Client\Models\BankIdNo\BankIdNoSignRequest;
use Identisure\Client\Models\BankIdNo\BankIdNoSignResult;
use Identisure\Client\Models\BankIdNo\BankIdNoSignedDocument;
use Identisure\Client\Models\BankIdNo\BankIdNoStatus;
use Identisure\Client\Models\BankIdNo\BankIdNoUnknown;
use Identisure\Client\PollOptions;
use Identisure\Client\Transport;
use Identisure\Client\WaitException;

final class BankIdNoEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(BankIdNoAuthRequest $req): BankIdNoStatus
    {
        return self::parseStatus($this->transport->post('/v3/bankid-no/auth', self::serialize($req)));
    }

    public function backchannelAuth(BankIdNoBackchannelAuthRequest $req): BankIdNoStatus
    {
        return self::parseStatus($this->transport->post('/v3/bankid-no/backchannel/auth', self::serialize($req)));
    }

    public function sign(BankIdNoSignRequest $req): BankIdNoStatus
    {
        return self::parseStatus($this->transport->post('/v3/bankid-no/sign', self::serialize($req)));
    }

    public function ageVerification(AgeVerificationRequest $req): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->post('/v3/bankid-no/age-verification', self::serialize($req)));
    }

    public function authStatus(string $id): BankIdNoStatus
    {
        return self::parseStatus($this->transport->get('/v3/bankid-no/auth/' . $id));
    }

    public function signStatus(string $id): BankIdNoStatus
    {
        return self::parseStatus($this->transport->get('/v3/bankid-no/sign/' . $id));
    }

    public function ageVerificationStatus(string $id): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->get('/v3/bankid-no/age-verification/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/bankid-no/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/bankid-no/sign/' . $id);
    }

    public function cancelAgeVerification(string $id): void
    {
        $this->transport->delete('/v3/bankid-no/age-verification/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): BankIdNoStatus
    {
        return $this->poll(fn() => $this->authStatus($id), $opts);
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): BankIdNoStatus
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

    private function poll(\Closure $fn, PollOptions $opts): BankIdNoStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $fn();
            if (!$status instanceof BankIdNoPending) {
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
    private static function parseStatus(array $d): BankIdNoStatus
    {
        return match ($d['status']) {
            'PENDING' => new BankIdNoPending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                url: $d['url'] ?? null,
                bindingMessage: $d['bindingMessage'] ?? null,
            ),
            'COMPLETED' => new BankIdNoCompleted(
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
                    ? new BankIdNoSignResult(
                        endUser: $d['signResult']['endUser'],
                        merchant: $d['signResult']['merchant'],
                        hash: $d['signResult']['hash'],
                    )
                    : null,
                signedDocuments: array_values(array_map(
                    fn(array $doc) => new BankIdNoSignedDocument(id: $doc['id'], hash: $doc['hash']),
                    $d['signedDocuments'] ?? [],
                )),
                authenticatedAt: $d['authenticatedAt'] ?? null,
                signedAt: $d['signedAt'] ?? null,
            ),
            'FAILED' => new BankIdNoFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new BankIdNoUnknown(status: $d['status']),
        };
    }
}
