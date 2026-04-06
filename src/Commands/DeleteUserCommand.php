<?php

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;

class DeleteUserCommand implements CommandInterface
{
    private UserRepositoryInterface $repository;
    private $userId;

    public function __construct(UserRepositoryInterface $repository, int $userId)
    {
        $this->repository = $repository;
        $this->userId = $userId;
    }

    public function execute(): void
    {
        $deleted = $this->repository->delete($this->userId);

        if ($deleted) {
            print_r("Пользователь с ID {$this->userId} успешно удален\n");
        } else {
            print_r("Пользователь с ID {$this->userId} не найден\n");
        }
    }
}