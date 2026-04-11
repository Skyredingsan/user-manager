<?php

declare(strict_types = 1);

namespace UserManager\Repositories;

use UserManager\Models\User;
use RuntimeException;
use JsonException;

final class JsonUserRepository implements UserRepositoryInterface {
    private array $users = [];

    public function __construct(
        private readonly string $filePath,
    ) {
        $this->load();
    }

    private function load(): void
    {
        if (!file_exists($this->filePath)) {
            $this->users = [];
            $this->saveToFile();
            return;
        }

        try {
            $content = file_get_contents($this->filePath);
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                $this->users = [];
                return;
            }

            $this -> users = [];
            foreach ($data as $userData) {
                if (isset($userData['id'])) {
                    $this -> users[$userData['id']] = User::fromArray($userData);
                }
            }
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to read user data: ' . $e->getMessage());
        }
    }

    private function saveToFile(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $data = array_map(
            static fn(User $user): array => toArray(),
            $this -> users
        );

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        file_put_contents($this->filePath, $json);
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function save(User $user): void
    {
        $this->users[$user->getId()] = $user;
        $this->saveToFile();
    }

    public function delete(int $id): bool
    {
        if (!isset($this->users[$id])) {
            return false;
        }
        unset($this->users[$id]);
        $this->saveToFile();
        return true;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function getNextId(): int
    {
        if ($this->users === []) {
            return 1;
        }

        return max(array_keys($this->users)) + 1;
    }
}