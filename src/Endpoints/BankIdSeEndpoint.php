<?php

declare(strict_types=1);

namespace Idkollen\Client\Endpoints;

use Idkollen\Client\Models\AgeVerification\AgeVerificationCompleted;
use Idkollen\Client\Models\AgeVerification\AgeVerificationFailed;
use Idkollen\Client\Models\AgeVerification\AgeVerificationPending;
use Idkollen\Client\Models\AgeVerification\AgeVerificationRequest;
use Idkollen\Client\Models\AgeVerification\AgeVerificationStatus;
use Idkollen\Client\Models\AgeVerification\AgeVerificationUnknown;
use Idkollen\Client\Models\BankIdSe\BankIdSeAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeCompleted;
use Idkollen\Client\Models\BankIdSe\BankIdSeDevice;
use Idkollen\Client\Models\BankIdSe\BankIdSeFailed;
use Idkollen\Client\Models\BankIdSe\BankIdSePending;
use Idkollen\Client\Models\BankIdSe\BankIdSePendingPhone;
use Idkollen\Client\Models\BankIdSe\BankIdSePhoneAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSePhoneSignRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSePhoneStatus;
use Idkollen\Client\Models\BankIdSe\BankIdSeSignRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeStepUp;
use Idkollen\Client\Models\BankIdSe\BankIdSeStatus;
use Idkollen\Client\Models\BankIdSe\BankIdSeUnknown;
use Idkollen\Client\Models\BankIdSe\BankIdSeVerifyRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeVerifyResponse;
use Idkollen\Client\PollOptions;
use Idkollen\Client\Transport;
use Idkollen\Client\WaitException;

final class BankIdSeEndpoint
{
    public function __construct(private readonly Transport $transport) {}

    public function auth(BankIdSeAuthRequest $req): BankIdSeStatus
    {
        return self::parseStatus($this->transport->post('/v3/bankid-se/auth', self::serialize($req)));
    }

    public function phoneAuth(BankIdSePhoneAuthRequest $req): BankIdSePhoneStatus
    {
        return self::parsePhoneStatus($this->transport->post('/v3/bankid-se/phone/auth', self::serialize($req)));
    }

    public function sign(BankIdSeSignRequest $req): BankIdSeStatus
    {
        return self::parseStatus($this->transport->post('/v3/bankid-se/sign', self::serialize($req)));
    }

    public function phoneSign(BankIdSePhoneSignRequest $req): BankIdSePhoneStatus
    {
        return self::parsePhoneStatus($this->transport->post('/v3/bankid-se/phone/sign', self::serialize($req)));
    }

    public function verify(BankIdSeVerifyRequest $req): BankIdSeVerifyResponse
    {
        $d = $this->transport->post('/v3/bankid-se/verify', self::serialize($req));
        return new BankIdSeVerifyResponse(
            ssn: $d['ssn'],
            name: $d['name'],
            givenName: $d['givenName'],
            surname: $d['surname'],
            age: $d['age'] ?? null,
            verifiedAt: $d['verifiedAt'] ?? null,
        );
    }

    public function ageVerification(AgeVerificationRequest $req): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->post('/v3/bankid-se/age-verification', self::serialize($req)));
    }

    public function authStatus(string $id): BankIdSeStatus
    {
        return self::parseStatus($this->transport->get('/v3/bankid-se/auth/' . $id));
    }

    public function signStatus(string $id): BankIdSeStatus
    {
        return self::parseStatus($this->transport->get('/v3/bankid-se/sign/' . $id));
    }

    public function ageVerificationStatus(string $id): AgeVerificationStatus
    {
        return self::parseAgeStatus($this->transport->get('/v3/bankid-se/age-verification/' . $id));
    }

    public function cancelAuth(string $id): void
    {
        $this->transport->delete('/v3/bankid-se/auth/' . $id);
    }

    public function cancelSign(string $id): void
    {
        $this->transport->delete('/v3/bankid-se/sign/' . $id);
    }

    public function cancelAgeVerification(string $id): void
    {
        $this->transport->delete('/v3/bankid-se/age-verification/' . $id);
    }

    public function waitForAuth(string $id, PollOptions $opts = new PollOptions()): BankIdSeStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $this->authStatus($id);
            if (!$status instanceof BankIdSePending) {
                return $status;
            }
            if (hrtime(true) >= $deadline) {
                throw new WaitException(timeout: true);
            }
            sleep($opts->interval);
        }
    }

    public function waitForSign(string $id, PollOptions $opts = new PollOptions()): BankIdSeStatus
    {
        $deadline = hrtime(true) + $opts->timeout * 1_000_000_000;
        while (true) {
            $status = $this->signStatus($id);
            if (!$status instanceof BankIdSePending) {
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

    private static function parseStatus(array $d): BankIdSeStatus
    {
        return match ($d['status']) {
            'PENDING' => new BankIdSePending(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                autoStartToken: $d['autoStartToken'] ?? null,
                qrStartToken: $d['qrStartToken'] ?? null,
                qrStartSecret: $d['qrStartSecret'] ?? null,
                hintCode: $d['hintCode'] ?? null,
            ),
            'COMPLETED' => new BankIdSeCompleted(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                ssn: $d['ssn'],
                name: $d['name'],
                givenName: $d['givenName'],
                surname: $d['surname'],
                certStartDate: $d['certStartDate'] ?? null,
                ocspResponse: $d['ocspResponse'],
                risk: $d['risk'] ?? null,
                device: isset($d['device']) ? new BankIdSeDevice(
                    ipAddress: $d['device']['ipAddress'] ?? null,
                    uhi: $d['device']['uhi'] ?? null,
                ) : null,
                stepUp: isset($d['stepUp']) ? new BankIdSeStepUp(
                    mrtd: $d['stepUp']['mrtd'] ?? null,
                    face: $d['stepUp']['face'] ?? null,
                ) : null,
                address: $d['address'] ?? null,
                companySignatoryText: $d['companySignatoryText'] ?? null,
                authenticatedAt: $d['authenticatedAt'] ?? null,
                signedAt: $d['signedAt'] ?? null,
            ),
            'FAILED' => new BankIdSeFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new BankIdSeUnknown(status: $d['status']),
        };
    }

    private static function parsePhoneStatus(array $d): BankIdSePhoneStatus
    {
        return match ($d['status']) {
            'PENDING' => new BankIdSePendingPhone(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                hintCode: $d['hintCode'] ?? null,
            ),
            'COMPLETED' => new BankIdSeCompleted(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                ssn: $d['ssn'],
                name: $d['name'],
                givenName: $d['givenName'],
                surname: $d['surname'],
                certStartDate: $d['certStartDate'] ?? null,
                ocspResponse: $d['ocspResponse'],
                risk: $d['risk'] ?? null,
                device: isset($d['device']) ? new BankIdSeDevice(
                    ipAddress: $d['device']['ipAddress'] ?? null,
                    uhi: $d['device']['uhi'] ?? null,
                ) : null,
                stepUp: isset($d['stepUp']) ? new BankIdSeStepUp(
                    mrtd: $d['stepUp']['mrtd'] ?? null,
                    face: $d['stepUp']['face'] ?? null,
                ) : null,
                address: $d['address'] ?? null,
                companySignatoryText: $d['companySignatoryText'] ?? null,
                authenticatedAt: $d['authenticatedAt'] ?? null,
                signedAt: $d['signedAt'] ?? null,
            ),
            'FAILED' => new BankIdSeFailed(
                id: $d['id'],
                refId: $d['refId'] ?? null,
                error: $d['error'],
            ),
            default => new BankIdSeUnknown(status: $d['status']),
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
