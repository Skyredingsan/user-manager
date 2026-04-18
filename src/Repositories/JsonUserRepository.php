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
        if (!file_exists($this->filePath))
        {
            $this->users = [];
            $this->saveToFile();
            return;
        }

        try {
            $content = file_get_contents($this->filePath);
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if (!is_array($data))
            {
                $this->users = [];
                return;
            }

            $this -> users = [];
            foreach ($data as $userData)
            {
                if (isset($userData['id']))
                {
                    $this -> users[$userData['id']] = User::fromArray($userData);
                }
            }
        } catch (JsonException $e)
        {
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
            fn(User $user): array => $user->toArray(),
            $this -> users,
        );

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $tempFile = $this->filePath . '.tmp';
        file_put_contents($tempFile, $json);
        rename($tempFile, $this->filePath);
    }

    public function getNextId(): int
    {
        if (empty($this->users)) {
            return 1;
        }
        return max(array_keys($this->users)) + 1;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function save(User $user): void
    {
        $existingId = $user->getId();

        if ($existingId > 0 && isset($this->users[$existingId]))
        {
            $this->users[$existingId] = $user;
            $this->saveToFile();
            return;
        }

        $newId = $this->getNextId();
        $user->setId($newId);

        $this->users[$newId] = $user;
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
}