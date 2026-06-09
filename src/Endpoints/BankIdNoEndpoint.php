<?php

declare(strict_types=1);

namespace Idkollen\Client\Endpoints;

use Idkollen\Client\Models\AgeVerification\AgeVerificationCompleted;
use Idkollen\Client\Models\AgeVerification\AgeVerificationFailed;
use Idkollen\Client\Models\AgeVerification\AgeVerificationPending;
use Idkollen\Client\Models\AgeVerification\AgeVerificationRequest;
use Idkollen\Client\Models\AgeVerification\AgeVerificationStatus;
use Idkollen\Client\Models\BankIdNo\BankIdNoAuthRequest;
use Idkollen\Client\Models\BankIdNo\BankIdNoBackchannelAuthRequest;
use Idkollen\Client\Models\BankIdNo\BankIdNoCompleted;
use Idkollen\Client\Models\BankIdNo\BankIdNoFailed;
use Idkollen\Client\Models\BankIdNo\BankIdNoPending;
use Idkollen\Client\Models\BankIdNo\BankIdNoSignRequest;
use Idkollen\Client\Models\BankIdNo\BankIdNoSignResult;
use Idkollen\Client\Models\BankIdNo\BankIdNoSignedDocument;
use Idkollen\Client\Models\BankIdNo\BankIdNoStatus;
use Idkollen\Client\PollOptions;
use Idkollen\Client\Transport;
use Idkollen\Client\WaitException;

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
        $deadline = time() + $opts->timeout;
        while (true) {
            $status = $this->ageVerificationStatus($id);
            if (!$status instanceof AgeVerificationPending) {
                return $status;
            }
            if (time() >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
    }

    private function poll(\Closure $fn, PollOptions $opts): BankIdNoStatus
    {
        $deadline = time() + $opts->timeout;
        while (true) {
            $status = $fn();
            if (!$status instanceof BankIdNoPending) {
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
            default => throw new \UnexpectedValueException("Unknown age verification status: {$d['status']}"),
        };
    }

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
                    ? new BankIdNoSignResult(
                        endUser: $d['signResult']['endUser'],
                        merchant: $d['signResult']['merchant'],
                        hash: $d['signResult']['hash'],
                    )
                    : null,
                signedDocuments: array_map(
                    fn(array $doc) => new BankIdNoSignedDocument(id: $doc['id'], hash: $doc['hash']),
                    $d['signedDocuments'] ?? [],
                ),
            ),
            'FAILED' => new BankIdNoFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => throw new \UnexpectedValueException("Unknown bankid-no status: {$d['status']}"),
        };
    }
}
