<?php

declare(strict_types=1);

namespace UserManager\Commands;

use UserManager\Services\UserService;
use UserManager\Exceptions\CliException;

final class ListUsersCommand implements CommandInterface
{
    public function __construct(
        private readonly UserService $service
    ) {}

    public function execute(): array
    {
        $users = $this->service->list();

        return [
            'users' => $users,
            'count' => count($users)
        ];
    }
}