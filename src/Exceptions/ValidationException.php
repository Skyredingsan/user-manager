<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

class ValidationException extends ApiException
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed', 400);
        $this->errors = $errors;
    }

    public function getErrorCode(): string
    {
        return 'VALIDATION_ERROR';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'fields' => $this->errors,
        ]);
    }
}