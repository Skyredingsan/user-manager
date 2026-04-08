<?php

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;

class DeleteUserCommand implements CommandInterface
{
    private UserRepositoryInterface $repository;
    private ?int $userId;

    public function __construct(UserRepositoryInterface $repository, ?int $userId = null)
    {
        $this->repository = $repository;
        $this->userId = $userId;
    }

    public function execute(): void
    {
        if ($this->userId === null || $this->userId <= 0) {
            echo "❌ Ошибка: ID пользователя должен быть положительным числом\n";
            echo "   Использование: php index.php delete <id>\n";
            return;
        };

        $this->repository->delete($this->userId);

        print_r("Пользователь с ID {$this->userId} успешно удален\n");

    }
}