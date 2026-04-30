<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

use Exception;

abstract class ApiException extends Exception
{
    abstract public function getStatusCode(): int;
    abstract public function getErrorCode(): string;
}