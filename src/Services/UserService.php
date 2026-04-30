<?php

declare(strict_types=1);

namespace UserManager\Services;

use UserManager\Models\User;
use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Exceptions\ValidationException;
use UserManager\Exceptions\NotFoundException;

final class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function list(): array
    {
        return $this->repository->findAll();
    }

    public function create(array $data): User
    {
        $this->validateCreateData($data);

        $email = $data['email'];
        $existing = $this->repository->findByEmail($email);
        if ($existing !== null) {
            throw new ValidationException("User with {$email} already exists");
        }

        $user = new User(0, $data['firstName'], $data['lastName'], $email);
        $this->repository->save($user);

        return $user;
    }

    public function delete(int $id): void
    {
        if ($id<=0) {
            throw new ValidationException("Invalid user id");
        }

        $user = $this->repository->findById($id);
        if ($user === null) {
            throw new NotFoundException("User with {$id} not found");
        }
        $this->repository->delete($id);
    }

    private function validateCreateData(array $data): void
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (!empty($errors)) {
            throw new ValidationException(json_encode($errors));
        }
    }
}