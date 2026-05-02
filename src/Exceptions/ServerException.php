<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

class ServerException extends ApiException
{
    public function __construct(string $message = 'Internal server error')
    {
        parent::__construct($message, 500);
    }

    public function getErrorCode(): string
    {
        return 'SERVER_ERROR';
    }
}