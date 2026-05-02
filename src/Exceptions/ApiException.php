<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

abstract class ApiException extends \Exception
{
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 500)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    abstract public function getErrorCode(): string;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
        ];
    }
}