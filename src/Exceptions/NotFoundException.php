<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

class NotFoundException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 404);
    }

    public function getStatusCode(): int
    {
        return 404;
    }

    public function getErrorCode(): string
    {
        return 'NOT_FOUND';
    }
}