<?php

declare(strict_types=1);

namespace UserManager\Http;

use UserManager\Services\UserService;
use UserManager\Models\User;

final class UserController
{
    public function __construct(
        private readonly UserService $service
    ) {}

    public function list(): array
    {
        $users = $this->service->list();

        $data = array_map(
            fn(User $user): array => $user->toArray(),
            $users
        );

        return [
            'data' => $data,
            'count' => count($data)
        ];
    }

    public function create(array $data): array
    {
        $user = $this->service->create($data);

        return [
            'data' => $user->toArray(),
            'message' => 'User created successfully'
        ];
    }

    public function delete(int $id): array
    {
        $this->service->delete($id);

        return [
            'message' => "User with ID {$id} deleted successfully"
        ];
    }
}