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

    public function execute(): array
    {
        $this->service->delete($this->userId);

        return [
            'message' => 'User deleted'
        ];
    }
}