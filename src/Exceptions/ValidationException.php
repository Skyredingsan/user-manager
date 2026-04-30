<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }

    public function getStatusCode(): int
    {
        return 400;
    }

    public function getErrorCode(): string
    {
        return 'VALIDATION_ERROR';
    }
}