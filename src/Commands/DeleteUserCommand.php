<?php

declare(strict_types=1);

namespace UserManager\Commands;

use UserManager\Services\UserService;
use UserManager\Exceptions\ValidationException;

final class DeleteUserCommand implements CommandInterface
{
    public function __construct(
        private readonly UserService $service,
        private readonly ?int $userId
    ) {}

    public function execute(): string
    {
        if ($this->userId === null || $this->userId <= 0) {
            throw new ValidationException("User ID must be not null and positive integer");
        }

        $this->service->delete($this->userId);

        return sprintf(" User with ID %d deleted successfully", $this->userId);
    }
}