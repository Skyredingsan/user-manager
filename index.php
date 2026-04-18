<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use UserManager\Commands\ListUsersCommand;
use UserManager\Commands\AddUserCommand;
use UserManager\Commands\DeleteUserCommand;
use UserManager\Repositories\RepositoryFactory;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbSource = $_ENV['DB_SOURCE'] ?? 'json';


try {
    $repository = match($dbSource) {
        'json' => new JsonUserRepository(__DIR__ . '/data/users.json'),
        'mysql' => new MysqlUserRepository([
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $_ENV['DB_NAME'] ?? 'user_manager',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
        ]),
        default => throw new \RuntimeException("Database error: {$dbSource}")
    };
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$commandName = $argv[1] ?? null;
$userId = isset($argv[2]) ? filter_var($argv[2], FILTER_VALIDATE_INT) : null;

try {
    $command = match($commandName) {
        'list' => new ListUsersCommand($repository),
        'add' => new AddUserCommand($repository),
        'delete' => new DeleteUserCommand($repository, $userId),
        default => throw new \InvalidArgumentException("Неизвестная команда: '{$commandName}'")
    };

    $command->execute();

} catch (\InvalidArgumentException $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "\nДоступные команды:\n";
    echo "  php index.php list           - показать всех пользователей\n";
    echo "  php index.php add            - добавить нового пользователя\n";
    echo "  php index.php delete <id>    - удалить пользователя по ID\n";
    exit(1);

} catch (\Throwable $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}