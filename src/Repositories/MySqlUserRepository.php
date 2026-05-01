<?php

declare(strict_types=1);

namespace UserManager\Repositories;

use UserManager\Models\User;
use UserManager\Exceptions\NotFoundException;
use UserManager\Exceptions\ServerException;
use PDO;
use PDOException;

final class MySqlUserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct(
        private readonly array $config
    ) {
        $this->connection = $this->connect();
        $this->initTable();
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

            return new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new ServerException("Database connection failed: " . $e->getMessage());
        }
    }

    private function initTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )";

        $this->connection->exec($sql);
    }

    private function rowToUser(array $row): User
    {
        return new User(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['email']
        );
    }

    public function list(): array
    {
        $stmt = $this->connection->query("SELECT * FROM users ORDER BY id");
        $rows = $stmt->fetchAll();

        return array_map(fn(array $row): User => $this->rowToUser($row), $rows);
    }

    public function create(User $user): User
    {
        $sql = "INSERT INTO users (first_name, last_name, email) VALUES (?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail()
        ]);

        $generatedId = (int)$this->connection->lastInsertId();
        $user->setId($generatedId);

        return $user;
    }

    public function delete(int $id): void
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            throw new NotFoundException("User with ID {$id} not found");
        }
    }

    public function findById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->rowToUser($row);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE LOWER(email) = LOWER(?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return $this->rowToUser($row);
    }
}