<?php

declare(strict_types=1);

namespace UserManager\Services;

use UserManager\Models\User;
use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Exceptions\ValidationException;

final class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function list(): array
    {
        return $this->repository->list();
    }

    public function create(array $data): User
    {
        $this->validateCreateData($data);

        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $email = $data['email'] ?? '';

        $existing = $this->repository->findByEmail($email);
        if ($existing !== null) {
            throw new ValidationException([
                'email' => "User with email '{$email}' already exists"
            ]);
        }

        $user = new User(0, $firstName, $lastName, $email);

        return $this->repository->create($user);
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new ValidationException([
                'id' => 'User ID must be a positive integer'
            ]);
        }

        $this->repository->delete($id);
    }

    private function validateCreateData(array $data): void
    {
        $errors = [];

        if (empty($data['firstName'])) {
            $errors['firstName'] = 'First name is required';
        }

        if (empty($data['lastName'])) {
            $errors['lastName'] = 'Last name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}