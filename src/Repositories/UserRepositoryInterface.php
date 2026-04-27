<?php

declare(strict_types=1);

namespace UserManager\Repositories;

use UserManager\Models\User;

interface UserRepositoryInterface
{
    public function findAll(): array;
    public function save(User $user): void;
    public function delete(int $id): bool;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
}