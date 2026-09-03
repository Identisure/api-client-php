<?php

declare(strict_types=1);

namespace Idkollen\Client\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use Idkollen\Client\IdkollenClient;
use Idkollen\Client\IdkollenClientBuilder;
use Idkollen\Client\IdkollenException;
use Idkollen\Client\Models\AgeVerification\AgeVerificationCompleted;
use Idkollen\Client\Models\AgeVerification\AgeVerificationFailed;
use Idkollen\Client\Models\AgeVerification\AgeVerificationPending;
use Idkollen\Client\Models\AgeVerification\AgeVerificationRequest;
use Idkollen\Client\Models\AgeVerification\AgeVerificationUnknown;
use Idkollen\Client\Models\BankIdNo\BankIdNoAuthRequest;
use Idkollen\Client\Models\BankIdNo\BankIdNoCompleted;
use Idkollen\Client\Models\BankIdNo\BankIdNoFailed;
use Idkollen\Client\Models\BankIdNo\BankIdNoPending;
use Idkollen\Client\Models\BankIdNo\BankIdNoSignRequest;
use Idkollen\Client\Models\BankIdNo\BankIdNoUnknown;
use Idkollen\Client\Models\BankIdSe\BankIdSeAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeCompleted;
use Idkollen\Client\Models\BankIdSe\BankIdSeFailed;
use Idkollen\Client\Models\BankIdSe\BankIdSePending;
use Idkollen\Client\Models\BankIdSe\BankIdSePendingPhone;
use Idkollen\Client\Models\BankIdSe\BankIdSePhoneAuthRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeSignRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeUnknown;
use Idkollen\Client\Models\BankIdSe\BankIdSeVerifyRequest;
use Idkollen\Client\Models\BankIdSe\BankIdSeVerifyResponse;
use Idkollen\Client\Models\Document\DocumentUploadResponse;
use Idkollen\Client\Models\Freja\FrejaAuthRequest;
use Idkollen\Client\Models\Freja\FrejaCompleted;
use Idkollen\Client\Models\Freja\FrejaFailed;
use Idkollen\Client\Models\Freja\FrejaPending;
use Idkollen\Client\Models\Freja\FrejaSignRequest;
use Idkollen\Client\Models\Freja\FrejaUnknown;
use Idkollen\Client\Models\Ftn\FtnAuthRequest;
use Idkollen\Client\Models\Ftn\FtnCompleted;
use Idkollen\Client\Models\Ftn\FtnFailed;
use Idkollen\Client\Models\Ftn\FtnPending;
use Idkollen\Client\Models\Ftn\FtnUnknown;
use Idkollen\Client\Models\MitId\MitIdAuthRequest;
use Idkollen\Client\Models\MitId\MitIdCompleted;
use Idkollen\Client\Models\MitId\MitIdFailed;
use Idkollen\Client\Models\MitId\MitIdPending;
use Idkollen\Client\Models\MitId\MitIdSignRequest;
use Idkollen\Client\Models\MitId\MitIdUnknown;
use Idkollen\Client\Models\Vipps\VippsAuthRequest;
use Idkollen\Client\Models\Vipps\VippsCompleted;
use Idkollen\Client\Models\Vipps\VippsFailed;
use Idkollen\Client\Models\Vipps\VippsPending;
use Idkollen\Client\Models\Vipps\VippsUnknown;
use Idkollen\Client\PollOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class E2eTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../../fixtures/';

    private MockHttpClient $http;
    private IdkollenClient $client;

    protected function setUp(): void
    {
        $this->http = new MockHttpClient();
        $factory = new HttpFactory();
        $this->client = (new IdkollenClientBuilder('cid', 'sec'))
            ->baseUrl('https://x.test')
            ->httpClient($this->http)
            ->requestFactory($factory)
            ->streamFactory($factory)
            ->build();
    }

    public static function providers(): array
    {
        return [
            'bankid_se' => ['bankid_se', 'bankIdSe', 'bankid-se', fn() => new BankIdSeAuthRequest(),
                BankIdSePending::class, BankIdSeCompleted::class, BankIdSeFailed::class, BankIdSeUnknown::class],
            'bankid_no' => ['bankid_no', 'bankIdNo', 'bankid-no', fn() => new BankIdNoAuthRequest(),
                BankIdNoPending::class, BankIdNoCompleted::class, BankIdNoFailed::class, BankIdNoUnknown::class],
            'freja' => ['freja', 'freja', 'freja', fn() => new FrejaAuthRequest(),
                FrejaPending::class, FrejaCompleted::class, FrejaFailed::class, FrejaUnknown::class],
            'mitid' => ['mitid', 'mitId', 'mitid', fn() => new MitIdAuthRequest(),
                MitIdPending::class, MitIdCompleted::class, MitIdFailed::class, MitIdUnknown::class],
            'ftn' => ['ftn', 'ftn', 'ftn', fn() => new FtnAuthRequest(),
                FtnPending::class, FtnCompleted::class, FtnFailed::class, FtnUnknown::class],
            'vipps' => ['vipps', 'vipps', 'vipps', fn() => new VippsAuthRequest(),
                VippsPending::class, VippsCompleted::class, VippsFailed::class, VippsUnknown::class],
        ];
    }

    public static function signProviders(): array
    {
        return [
            'bankid_se' => ['bankid_se', 'bankIdSe', 'bankid-se', fn() => new BankIdSeSignRequest(text: 'Sign this'),
                BankIdSeCompleted::class],
            'bankid_no' => ['bankid_no', 'bankIdNo', 'bankid-no', fn() => new BankIdNoSignRequest(text: 'Sign this'),
                BankIdNoCompleted::class],
            'freja' => ['freja', 'freja', 'freja', fn() => new FrejaSignRequest(text: 'Sign this'),
                FrejaCompleted::class],
            'mitid' => ['mitid', 'mitId', 'mitid', fn() => new MitIdSignRequest(text: 'Sign this'),
                MitIdCompleted::class],
        ];
    }

    #[DataProvider('providers')]
    public function testAuthCompleted(
        string $dir, string $endpoint, string $segment, \Closure $request,
        string $pending, string $completed, string $failed, string $unknown,
    ): void {
        $result = $this->runAuth($dir, $endpoint, $segment, $request, $pending, 'auth_completed');

        self::assertInstanceOf($completed, $result);
        self::assertMatchesFixture(self::json("$dir/auth_completed.json"), $result);
    }

    #[DataProvider('providers')]
    public function testAuthFailed(
        string $dir, string $endpoint, string $segment, \Closure $request,
        string $pending, string $completed, string $failed, string $unknown,
    ): void {
        $result = $this->runAuth($dir, $endpoint, $segment, $request, $pending, 'auth_failed');

        self::assertInstanceOf($failed, $result);
        self::assertSame('CANCELLED', $result->error);
        self::assertMatchesFixture(self::json("$dir/auth_failed.json"), $result);
    }

    #[DataProvider('providers')]
    public function testAuthUnknown(
        string $dir, string $endpoint, string $segment, \Closure $request,
        string $pending, string $completed, string $failed, string $unknown,
    ): void {
        $result = $this->runAuth($dir, $endpoint, $segment, $request, $pending, 'auth_unknown');

        self::assertInstanceOf($unknown, $result);
        self::assertSame('SOMETHING_NEW', $result->status);
    }

    #[DataProvider('signProviders')]
    public function testSignCompleted(
        string $dir, string $endpoint, string $segment, \Closure $request, string $completed,
    ): void {
        $this->http->enqueue(201, self::raw("$dir/auth_pending.json"));
        $this->http->enqueue(200, self::raw("$dir/auth_pending.json"));
        $this->http->enqueue(200, self::raw("$dir/sign_completed.json"));

        $api = $this->client->$endpoint();
        $session = $api->sign($request());
        $result = $api->waitForSign($session->id, new PollOptions(interval: 0));

        $id = self::json("$dir/auth_pending.json")['id'];
        $this->assertRequests([
            ['POST', "/v3/$segment/sign"],
            ['GET', "/v3/$segment/sign/$id"],
            ['GET', "/v3/$segment/sign/$id"],
        ]);
        self::assertInstanceOf($completed, $result);
        self::assertMatchesFixture(self::json("$dir/sign_completed.json"), $result);
    }

    public function testBankIdSePhoneAuth(): void
    {
        $this->http->enqueue(201, self::raw('bankid_se/phone_auth_pending.json'));
        $this->http->enqueue(200, self::raw('bankid_se/phone_auth_pending.json'));
        $this->http->enqueue(200, self::raw('bankid_se/auth_completed.json'));

        $api = $this->client->bankIdSe();
        $session = $api->phoneAuth(new BankIdSePhoneAuthRequest(ssn: '199001011239', callInitiator: 'RP'));
        self::assertInstanceOf(BankIdSePendingPhone::class, $session);
        $result = $api->waitForAuth($session->id, new PollOptions(interval: 0));

        $id = self::json('bankid_se/phone_auth_pending.json')['id'];
        $this->assertRequests([
            ['POST', '/v3/bankid-se/phone/auth'],
            ['GET', "/v3/bankid-se/auth/$id"],
            ['GET', "/v3/bankid-se/auth/$id"],
        ]);
        self::assertInstanceOf(BankIdSeCompleted::class, $result);
        self::assertMatchesFixture(self::json('bankid_se/auth_completed.json'), $result);
    }

    public function testBankIdSeVerify(): void
    {
        $this->http->enqueue(200, self::raw('bankid_se/verify.json'));

        $result = $this->client->bankIdSe()->verify(new BankIdSeVerifyRequest(qrCode: 'bankid.qr'));

        $this->assertRequests([['POST', '/v3/bankid-se/verify']]);
        self::assertInstanceOf(BankIdSeVerifyResponse::class, $result);
        self::assertMatchesFixture(self::json('bankid_se/verify.json'), $result);
    }

    public static function ageVerificationOutcomes(): array
    {
        return [
            'completed' => ['completed', AgeVerificationCompleted::class],
            'failed' => ['failed', AgeVerificationFailed::class],
            'unknown' => ['unknown', AgeVerificationUnknown::class],
        ];
    }

    #[DataProvider('ageVerificationOutcomes')]
    public function testBankIdSeAgeVerification(string $outcome, string $class): void
    {
        $this->http->enqueue(201, self::raw('age_verification/pending.json'));
        $this->http->enqueue(200, self::raw('age_verification/pending.json'));
        $this->http->enqueue(200, self::raw("age_verification/$outcome.json"));

        $api = $this->client->bankIdSe();
        $session = $api->ageVerification(new AgeVerificationRequest(minAge: 18));
        self::assertInstanceOf(AgeVerificationPending::class, $session);
        self::assertMatchesFixture(self::json('age_verification/pending.json'), $session);
        $result = $api->waitForAgeVerification($session->id, new PollOptions(interval: 0));

        $id = self::json('age_verification/pending.json')['id'];
        $this->assertRequests([
            ['POST', '/v3/bankid-se/age-verification'],
            ['GET', "/v3/bankid-se/age-verification/$id"],
            ['GET', "/v3/bankid-se/age-verification/$id"],
        ]);
        self::assertInstanceOf($class, $result);
        if ($result instanceof AgeVerificationUnknown) {
            self::assertSame('SOMETHING_NEW', $result->status);
        } else {
            self::assertMatchesFixture(self::json("age_verification/$outcome.json"), $result);
        }
    }

    public function testDocumentUpload(): void
    {
        $this->http->enqueue(201, self::raw('document/upload.json'));

        $result = $this->client->document()->upload('%PDF-1.7', 'contract.pdf');

        $this->assertRequests([['POST', '/v3/document']]);
        self::assertInstanceOf(DocumentUploadResponse::class, $result);
        self::assertMatchesFixture(self::json('document/upload.json'), $result);
    }

    public function testErrorResponse(): void
    {
        $this->http->enqueue(400, self::raw('error.json'));

        try {
            $this->client->ftn()->auth(new FtnAuthRequest());
            self::fail('Expected IdkollenException');
        } catch (IdkollenException $e) {
            self::assertSame(400, $e->statusCode);
            self::assertStringContainsString(self::json('error.json')['message'], $e->getMessage());
        }
    }

    private function runAuth(
        string $dir, string $endpoint, string $segment, \Closure $request, string $pending, string $outcome,
    ): object {
        $this->http->enqueue(201, self::raw("$dir/auth_pending.json"));
        $this->http->enqueue(200, self::raw("$dir/auth_pending.json"));
        $this->http->enqueue(200, self::raw("$dir/$outcome.json"));

        $api = $this->client->$endpoint();
        $session = $api->auth($request());
        self::assertInstanceOf($pending, $session);
        self::assertMatchesFixture(self::json("$dir/auth_pending.json"), $session);
        $result = $api->waitForAuth($session->id, new PollOptions(interval: 0));

        $id = self::json("$dir/auth_pending.json")['id'];
        $this->assertRequests([
            ['POST', "/v3/$segment/auth"],
            ['GET', "/v3/$segment/auth/$id"],
            ['GET', "/v3/$segment/auth/$id"],
        ]);

        return $result;
    }

    /** @param list<array{string, string}> $expected */
    private function assertRequests(array $expected): void
    {
        $requests = $this->http->requests();
        self::assertCount(count($expected), $requests);
        foreach ($expected as $i => [$method, $path]) {
            self::assertSame($method, $requests[$i]->getMethod());
            self::assertSame($path, $requests[$i]->getUri()->getPath());
            self::assertSame('Basic ' . base64_encode('cid:sec'), $requests[$i]->getHeaderLine('Authorization'));
        }
    }

    private static function assertMatchesFixture(array $fixture, object $actual): void
    {
        unset($fixture['status']);
        self::assertMatchesValue($fixture, $actual, '$');
    }

    private static function assertMatchesValue(mixed $expected, mixed $actual, string $path): void
    {
        if (is_array($expected) && array_is_list($expected)) {
            self::assertIsArray($actual, $path);
            self::assertCount(count($expected), $actual, $path);
            foreach ($expected as $i => $item) {
                self::assertMatchesValue($item, $actual[$i], "{$path}[$i]");
            }
        } elseif (is_array($expected)) {
            self::assertIsObject($actual, $path);
            foreach ($expected as $key => $value) {
                self::assertTrue(property_exists($actual, $key), "Missing property $path.$key");
                self::assertMatchesValue($value, $actual->$key, "$path.$key");
            }
        } else {
            self::assertSame($expected, $actual, $path);
        }
    }

    private static function raw(string $name): string
    {
        $contents = file_get_contents(self::FIXTURES . $name);
        self::assertIsString($contents, "Missing fixture $name");

        return $contents;
    }

    private static function json(string $name): array
    {
        return json_decode(self::raw($name), true, flags: JSON_THROW_ON_ERROR);
    }
}
