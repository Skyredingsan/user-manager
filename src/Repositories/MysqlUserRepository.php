<?php

declare (strict_types = 1);

namespace UserManager\Repositories;

use UserManager\Models\User;
use PDO;
use PDOException;
use RuntimeException;

final class MysqlUserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct(
        private readonly array $config
    ) {
        $this->connection = $this->connect();
        $this->createTableIfNotExists();
    }

    private function connect(): PDO
    {
        try {
            $host = $this->config['host'] ?? 'localhost';
            $port = $this->config['port'] ?? 3306;
            $dbname = $this->config['dbname'] ?? 'user_manager';
            $user = $this->config['user'] ?? 'root';
            $password = $this->config['password'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            return $pdo;
        } catch (PDOException $e)
        {
            throw new RuntimeException("MySQL connection failed: " . $e->getMessage());
        }
    }

    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )";

        $this->connection->exec($sql);
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM users ORDER BY id");
        $rows = $stmt->fetchAll();

        $users = [];
        foreach ($rows as $row) {
            $users[] = new User(
                (int)$row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['email']
            );
        }

        return $users;
    }

    public function save(User $user): void
    {

        $existingId = $user->getId();

        if ($existingId > 0 && $this->findById($existingId) !== null)
        {
            $sql = "UPDATE users SET first_name= ? , last_name= ?, email= ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute
            ([
                $user->getFirstName(),
                $user->getLastName(),
                $user->getEmail(),
                $user->getId()
            ]);
            return;
        }

        $sql = "INSERT INTO users (first_name, last_name, email) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute
        ([
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail()
        ]);

        $lastId = (int)$this->connection->lastInsertId();
        $user->setId($lastId);
    }

    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM users WHERE id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function findById(int $id): ?User
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return new User(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email']
        );
    }

    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM users WHERE email = ?';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return new User(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email']
        );
    }
}