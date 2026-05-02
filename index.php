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
use UserManager\Exceptions\NotFoundException;
use UserManager\Exceptions\ValidationException;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $repository = RepositoryFactory::create($_ENV['DB_SOURCE'] ?? 'json');
    $service = new UserService($repository);

    $commandName = $argv[1] ?? null;

    if (!$commandName) {
        throw new ValidationException([
            'command' => 'Command is required'
        ]);
    }

    $result = match ($commandName) {

        'list' => (new ListUsersCommand($service))->execute(),

        'add' => (new AddUserCommand($service))->execute(),

        'delete' => function() use ($service, $argv) {
            $id = $argv[2] ?? null;

            if (!$id || !is_numeric($id)) {
                throw new ValidationException([
                    'id' => 'Valid user ID is required'
                ]);
            }

            return (new DeleteUserCommand($service, (int)$id))->execute();
        },

        default => throw new NotFoundException("Unknown command: {$commandName}")
    };

    $data = is_callable($result) ? $result() : $result;

    echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

} catch (ApiException $e) {

    echo json_encode([
            'success' => false,
            'error' => $e->toArray()
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    exit($e->getStatusCode());

} catch (\Throwable $e) {

    echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => $e->getMessage()
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

    exit(1);
}