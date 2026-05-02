<?php

declare(strict_types=1);

namespace UserManager\Repositories;

final class RepositoryFactory
{
    public static function create(string $source): UserRepositoryInterface
    {
        // Загружаем конфигурацию из $_ENV
        $config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $_ENV['DB_NAME'] ?? 'user_manager',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
        ];

        return match ($source) {
            'json' => new JsonUserRepository(__DIR__ . '/../../data/users.json'),
            'mysql' => new MySqlUserRepository($config),
            default => throw new \InvalidArgumentException("Unknown source: {$source}")
        };
    }
}