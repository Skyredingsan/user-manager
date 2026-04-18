<?php

declare(strict_types=1);

namespace UserManager\Repositories;

class RepositoryFactory
{
    public static function create(string $source, array $config = []): UserRepositoryInterface
    {
        return match ($source) {
            'json' => new JsonUserRepository($config['json_path'] ?? __DIR__ . '/../../data/users.json'),
            'mysql' => new MysqlUserRepository($config['mysql_path'] ?? []),
            default => throw new \InvalidArgumentException("Database error: {$source}")
        };
    }
}