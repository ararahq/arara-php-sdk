<?php

declare(strict_types=1);

namespace Arara\Tests\Unit\Exceptions;

use Arara\Exceptions\AraraException;
use Arara\Exceptions\AuthenticationException;
use Arara\Exceptions\BadRequestException;
use Arara\Exceptions\InternalServerException;
use Arara\Exceptions\NotFoundException;
use Arara\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

final class AraraExceptionTest extends TestCase
{
    public function test_exception_uses_response_message(): void
    {
        $exception = new AraraException(400, ['message' => 'Bad request body']);

        $this->assertSame('Bad request body', $exception->getMessage());
        $this->assertSame(400, $exception->statusCode);
        $this->assertSame(['message' => 'Bad request body'], $exception->response);
    }

    public function test_exception_falls_back_to_http_status(): void
    {
        $exception = new AraraException(503);

        $this->assertSame('HTTP 503', $exception->getMessage());
        $this->assertSame(503, $exception->statusCode);
        $this->assertNull($exception->response);
    }

    public function test_exception_uses_custom_message(): void
    {
        $exception = new AraraException(422, null, 'Campo obrigatório');

        $this->assertSame('Campo obrigatório', $exception->getMessage());
        $this->assertSame(422, $exception->statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('specificExceptionsProvider')]
    public function test_specific_exceptions_set_correct_status_code(string $class, int $expectedStatus): void
    {
        /** @var AraraException $exception */
        $exception = new $class();

        $this->assertSame($expectedStatus, $exception->statusCode);
        $this->assertInstanceOf(AraraException::class, $exception);
    }

    /**
     * @return array<string, array{string, int}>
     */
    public static function specificExceptionsProvider(): array
    {
        return [
            'BadRequest' => [BadRequestException::class, 400],
            'Authentication' => [AuthenticationException::class, 401],
            'NotFound' => [NotFoundException::class, 404],
            'Validation' => [ValidationException::class, 422],
            'InternalServer' => [InternalServerException::class, 500],
        ];
    }

    public function test_validation_exception_accepts_custom_message(): void
    {
        $exception = new ValidationException(message: 'O campo receiver é obrigatório.');

        $this->assertSame('O campo receiver é obrigatório.', $exception->getMessage());
        $this->assertSame(422, $exception->statusCode);
        $this->assertNull($exception->response);
    }
}
