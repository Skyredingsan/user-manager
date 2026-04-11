<?php

declare (strict_types = 1);

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;

final class DeleteUserCommand implements CommandInterface
{

    public function __construct(
        private readonly UserRepositoryInterface $repository,
        private readonly ?int $userId
        )
    {}

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