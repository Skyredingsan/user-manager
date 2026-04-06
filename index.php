<?php

require_once __DIR__ . '/vendor/autoload.php';

use UserManager\Repositories\JsonUserRepository;
use UserManager\Commands\ListUsersCommand;
use UserManager\Commands\AddUserCommand;
use UserManager\Commands\DeleteUserCommand;

$repository = new JsonUserRepository(__DIR__ . '/data/users.json');

$commandName = $argv[1] ?? null;
$argument = $argv[2] ?? null;

try {
    switch ($commandName) {
        case 'list':
            $command = new ListUsersCommand($repository);
            $command->execute();
            break;

        case 'add':
            $command = new AddUserCommand($repository);
            $command->execute();
            break;

        case 'delete':
            if ($argument === null) {
                echo "Ошибка: укажите ID пользователя для удаления\n";
                echo "Пример: php index.php delete 5\n";
                exit(1);
            }

            $userId = (int)$argument;
            if ($userId <= 0) {
                echo "Ошибка: ID должен быть положительным числом\n";
                exit(1);
            }

            $command = new DeleteUserCommand($repository, $userId);
            $command->execute();
            break;

        default:
            echo "Доступные команды:\n";
            echo "  php index.php list           - показать всех пользователей\n";
            echo "  php index.php add            - добавить нового пользователя\n";
            echo "  php index.php delete <id>    - удалить пользователя по ID\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}