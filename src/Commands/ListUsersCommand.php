<?php

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;

class ListUsersCommand implements CommandInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): void
    {
        $users = $this->repository->findAll();

        if (empty($users)) {
            print_r("Список пользователей пуст\n");
            return;
        }

        $this->printTable($users);
    }

    private function printSeparator(array $widths): void
    {
        echo "+";
        foreach ($widths as $width) {
            echo str_repeat("-", $width) . "+";
        }
        echo "\n";
    }

    private function printHeader(array $widths): void
    {
        printf("| %-{$widths[0]}s | %-{$widths[1]}s | %-{$widths[2]}s | %-{$widths[3]}s |\n",
            "ID", "First Name", "Last Name", "Email");
    }

    private function printTable(array $users): void
    {
        // Фиксированная ширина колонок
        $idWidth = 4;
        $firstNameWidth = 12;
        $lastNameWidth = 12;
        $emailWidth = 20;

        // Шапка таблицы
        echo str_repeat("-", $idWidth + $firstNameWidth + $lastNameWidth + $emailWidth + 13) . "\n";
        printf("| %-{$idWidth}s | %-{$firstNameWidth}s | %-{$lastNameWidth}s | %-{$emailWidth}s |\n",
            "ID", "First Name", "Last Name", "Email");
        echo str_repeat("-", $idWidth + $firstNameWidth + $lastNameWidth + $emailWidth + 13) . "\n";

        // Данные
        foreach ($users as $user) {
            printf("| %-{$idWidth}d | %-{$firstNameWidth}s | %-{$lastNameWidth}s | %-{$emailWidth}s |\n",
                $user->getId(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail()
            );
        }

        echo str_repeat("-", $idWidth + $firstNameWidth + $lastNameWidth + $emailWidth + 13) . "\n";
    }
}