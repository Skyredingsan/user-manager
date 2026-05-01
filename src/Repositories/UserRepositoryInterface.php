<?php

declare(strict_types=1);

namespace UserManager\Repositories;

use UserManager\Models\User;

interface UserRepositoryInterface
{
    public function list(): array;
    public function create(User $user): User;
    public function delete(int $id): void;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
}