<?php

declare(strict_types=1);

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Models\User;

final class ListUsersCommand implements CommandInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(): void
    {
        $users = $this->repository->findAll();

        if (empty($users)) {
            print_r("Список пользователей пуст\n");
            return;
        }

        $this->printTable($users);
    }

    private function printTable(array $users): void
    {
        $width = 4;
        $widthFirstName = 12;
        $widthLastName = 12;
        $widthEmail = 25;

        $separator = '+' . str_repeat('-', $width) .
            '+' . str_repeat('-', $widthFirstName) .
            '+' . str_repeat('-', $widthLastName) .
            '+' . str_repeat('-', $widthEmail) . "+\n";

        echo $separator;
        printf("| %-{$width}s | %-{$widthFirstName}s | %-{$widthLastName}s | %-{$widthEmail}s |\n",
            "ID", "First Name", "Last Name", "Email");
        echo $separator;

        foreach ($users as $user) {
            printf("| %-{$width}d | %-{$widthFirstName}s | %-{$widthLastName}s | %-{$widthEmail}s |\n",
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail()
            );
        }

        echo $separator;
    }
}