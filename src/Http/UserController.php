<?php

declare(strict_types=1);

namespace UserManager\Http;

use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Models\User;

final class UserController
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    )
    {
    }

    public function list(): array
    {
        $users = $this->repository->findAll();

        $data = array_map(
            static fn(User $user): array => $user->toArray(),
            $users
        );

        return [
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ];
    }


    public function create(array $data): array
    {
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $email = $data['email'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($email)) {
            return [
                'success' => false,
                'message' => 'Не заполнены обязательные поля: firstName, lastName, email.',
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Неверный формат почты'
            ];
        }

        $existinbg = $this->repository->findByEmail($email);
        if ($existinbg !== null) {
            return [
                'success' => false,
                'message' => 'Почта не уникальна'
            ];
        }

        $user = new User(0, $firstName, $lastName, $email);
        $this->repository->save($user);

        return [
            'success' => true,
            'data' => $user->toArray(),
            'message' => 'Успешное создание юзера'
        ];
    }

    public function delete(int $id): array
    {
        if ($id <= 0 || $id === null)
        {
            return [
                'success' => false,
                'message' => 'invalid id'
            ];
        }

        $deleted = $this->repository->delete($id);

        if ($deleted) {
            return [
                'success' => true,
                'message' => "User with ID {$id} deleted successfully."
            ];
        }

        return [
            'success' => false,
            'message' => "User with ID {$id} not found."
        ];
    }
}