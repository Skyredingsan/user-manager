<?php

declare(strict_types=1);

namespace UserManager\Exceptions;

use Exception;

class CliException extends Exception
{
    private string $exitMessage;

    public function __construct(string $message, int $code = 1)
    {
        parent::__construct($message, $code);
        $this->exitMessage = $message;
    }

    public function getExitMessage(): string
    {
        return $this->exitMessage;
    }
}