<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use UserManager\Repositories\RepositoryFactory;
use UserManager\Services\UserService;
use UserManager\Commands\ListUsersCommand;
use UserManager\Commands\AddUserCommand;
use UserManager\Commands\DeleteUserCommand;
use UserManager\Exceptions\ApiException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $repository = RepositoryFactory::create($_ENV['DB_SOURCE'] ?? 'json');
    $service = new UserService($repository);

    $commandName = $argv[1] ?? null;
    $userId = isset($argv[2]) ? filter_var($argv[2], FILTER_VALIDATE_INT) : null;

    $result = match($commandName) {
        'list' => (new ListUsersCommand($service))->execute(),
        'add' => (new AddUserCommand($service))->execute(),
        'delete' => (new DeleteUserCommand($service, $userId))->execute(),
        default => throw new \InvalidArgumentException("Unknown command: {$commandName}")
    };

    echo $result . "\n";

} catch (ApiException $e) {
    echo "❌ " . $e->getMessage() . "\n";
    exit($e->getCode() ?: 1);
} catch (\InvalidArgumentException $e) {
    echo " Usage: php index.php [list|add|delete <id>]\n";
    exit(1);
} catch (\Throwable $e) {
    echo " Error: " . $e->getMessage() . "\n";
    exit(1);
}