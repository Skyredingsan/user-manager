<?php

namespace UserManager\Repositories;

use UserManager\Models\User;

class JsonUserRepository implements UserRepositoryInterface {
    private string $filePath;
    private array $users = [];

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        $this->load();
    }

    private function load(): void {
        if (!file_exists($this->filePath)) {
            $this->users = [];
            $this->saveToFile();
            return;
        }

        $content = file_get_contents($this->filePath);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            $this->users = [];
            return;
        }

        $this->users = [];
        foreach ($data as $userData) {
            $this->users[$userData['id']] = User::fromArray($userData);
        }
    }

    private function saveToFile(): void {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $data = [];
        foreach ($this->users as $user) {
            $data[] = $user->toArray();
        }

        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    }

    public function findAll(): array {
        return array_values($this->users);
    }

    public function save(User $user): void {
        $this->users[$user->getId()] = $user;
        $this->saveToFile();
    }

    public function delete(int $id): bool {
        if (!isset($this->users[$id])) {
            return false;
        }
        unset($this->users[$id]);
        $this->saveToFile();
        return true;
    }

    public function findById(int $id): ?User {
        return $this->users[$id] ?? null;
    }

    public function getNextId(): int
    {
        if (empty($this->users)) {
            return 1;
        }

        $ids = array_keys($this->users);
        return max($ids) + 1;
    }
}