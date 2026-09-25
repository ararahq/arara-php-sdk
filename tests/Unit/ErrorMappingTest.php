<?php

declare(strict_types=1);

namespace Arara\Tests\Unit;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\ForbiddenException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\PlanFeatureLockedException;
use Arara\Exceptions\RateLimitException;
use Arara\Exceptions\ValidationException;
use Arara\Resources\Templates;
use Arara\Tests\Support\RecordingClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ErrorMappingTest extends TestCase
{
    public function test_403_plan_feature_locked_becomes_typed_exception_with_details(): void
    {
        $body = '{"error":{"code":"PLAN_FEATURE_LOCKED","message":"Essa feature está liberada a partir do plano Voo.","details":{"feature":"canUseFlows","currentPlan":"DECOLAGEM","upgradeTo":"VOO"}}}';

        $e = $this->capture(new Response(403, [], $body));

        $this->assertInstanceOf(PlanFeatureLockedException::class, $e);
        $this->assertSame(403, $e->statusCode);
        $this->assertSame('PLAN_FEATURE_LOCKED', $e->errorCode);
        $this->assertSame('canUseFlows', $e->feature);
        $this->assertSame('DECOLAGEM', $e->currentPlan);
        $this->assertSame('VOO', $e->upgradeTo);
        $this->assertSame(['feature' => 'canUseFlows', 'currentPlan' => 'DECOLAGEM', 'upgradeTo' => 'VOO'], $e->details);
    }

    public function test_403_without_envelope_is_forbidden_with_body_message(): void
    {
        $e = $this->capture(new Response(403, [], '{"timestamp":"x","status":403,"error":"Forbidden","message":"API Key permission insufficient","path":"/v1/templates"}'));

        $this->assertInstanceOf(ForbiddenException::class, $e);
        $this->assertNotInstanceOf(AuthenticationException::class, $e);
        $this->assertSame(403, $e->statusCode);
        $this->assertNull($e->errorCode);
        $this->assertSame('API Key permission insufficient', $e->getMessage());
    }

    public function test_403_with_empty_body_is_forbidden(): void
    {
        $e = $this->capture(new Response(403));

        $this->assertInstanceOf(ForbiddenException::class, $e);
        $this->assertSame('HTTP 403', $e->getMessage());
    }

    public function test_403_with_plain_text_body_uses_text_as_message(): void
    {
        $e = $this->capture(new Response(403, [], 'Access Denied'));

        $this->assertInstanceOf(ForbiddenException::class, $e);
        $this->assertSame('Access Denied', $e->getMessage());
    }

    public function test_404_with_empty_body_is_not_found(): void
    {
        $e = $this->capture(new Response(404));

        $this->assertInstanceOf(NotFoundException::class, $e);
        $this->assertSame(404, $e->statusCode);
    }

    public function test_403_with_other_business_code_is_forbidden(): void
    {
        $e = $this->capture(new Response(403, [], '{"error":{"code":"RESOURCE_FORBIDDEN","message":"no"}}'));

        $this->assertInstanceOf(ForbiddenException::class, $e);
        $this->assertNotInstanceOf(PlanFeatureLockedException::class, $e);
        $this->assertSame('RESOURCE_FORBIDDEN', $e->errorCode);
    }

    public function test_401_is_authentication(): void
    {
        $e = $this->capture(new Response(401, [], '{"message":"Invalid API key"}'));

        $this->assertInstanceOf(AuthenticationException::class, $e);
        $this->assertSame(401, $e->statusCode);
    }

    public function test_status_codes_map_to_their_exceptions(): void
    {
        $this->assertInstanceOf(BadRequestException::class, $this->capture(new Response(400, [], '{"error":{"code":"INVALID_PATH_PARAM"}}')));
        $this->assertInstanceOf(ValidationException::class, $this->capture(new Response(422, [], '{"error":{"code":"INVALID_RECIPIENT"}}')));
        $this->assertSame(409, $this->capture(new Response(409, [], '{"error":{"code":"CONFLICT"}}'))->statusCode);
    }

    public function test_429_exposes_retry_after_after_retries_are_exhausted(): void
    {
        $e = $this->capture(...array_fill(0, 4, new Response(429, ['Retry-After' => '0'], '{"error":{"code":"BATCH_BUSY","message":"busy"}}')));

        $this->assertInstanceOf(RateLimitException::class, $e);
        $this->assertSame(0, $e->retryAfter);
        $this->assertSame('BATCH_BUSY', $e->errorCode);
    }

    public function test_503_is_server_exception_with_real_status_and_retry_after(): void
    {
        $e = $this->capture(...array_fill(0, 4, new Response(503, ['Retry-After' => '0'], '{"error":{"code":"SEND_TEMPORARILY_UNAVAILABLE"}}')));

        $this->assertInstanceOf(InternalServerException::class, $e);
        $this->assertSame(503, $e->statusCode);
        $this->assertSame(0, $e->retryAfter);
        $this->assertSame('SEND_TEMPORARILY_UNAVAILABLE', $e->errorCode);
    }

    private function capture(Response ...$responses): AraraException
    {
        $http = new RecordingClient(array_values($responses));

        try {
            (new Templates($http->client))->get('id');
        } catch (AraraException $e) {
            return $e;
        }

        $this->fail('Expected AraraException');
    }
}
