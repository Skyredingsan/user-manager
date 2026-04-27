<?php

declare(strict_types=1);

namespace UserManager\Repositories;

use UserManager\Models\User;
use RuntimeException;
use JsonException;

final class JsonUserRepository implements UserRepositoryInterface
{
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

        $fp = fopen($this->filePath, 'r');
        if (!$fp) {
            throw new RuntimeException("Cannot open file: {$this->filePath}");
        }

        if (!flock($fp, LOCK_SH)) {
            fclose($fp);
            throw new RuntimeException("Cannot lock file for reading");
        }

        try {
            $content = stream_get_contents($fp);
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                $this->users = [];
                return;
            }

            $this->users = [];
            foreach ($data as $userData) {
                if (isset($userData['id'])) {
                    $this->users[$userData['id']] = User::fromArray($userData);
                }
            }
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to read user data: ' . $e->getMessage());
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
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
            $this->users,
        );

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        // Атомарная запись через временный файл
        $tempFile = $this->filePath . '.tmp';
        file_put_contents($tempFile, $json);
        rename($tempFile, $this->filePath);
    }

    private function getNextId(): int
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
        $fp = fopen($this->filePath, 'c+');
        if (!$fp) {
            throw new RuntimeException("Cannot open file for save");
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            throw new RuntimeException("Cannot lock file for writing");
        }

        try {
            fseek($fp, 0);
            $content = stream_get_contents($fp);
            if ($content) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    $this->users = [];
                    foreach ($data as $userData) {
                        if (isset($userData['id'])) {
                            $this->users[$userData['id']] = User::fromArray($userData);
                        }
                    }
                }
            }

            $existingId = $user->getId();

            if ($existingId > 0 && isset($this->users[$existingId])) {
                $this->users[$existingId] = $user;
            } else {
                $newId = $this->getNextId();
                $user->setId($newId);
                $this->users[$newId] = $user;
            }

            $data = array_map(
                fn(User $u): array => $u->toArray(),
                $this->users,
            );

            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);

        } catch (JsonException $e) {
            throw new RuntimeException('Failed to save user data: ' . $e->getMessage());
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    public function delete(int $id): bool
    {
        $fp = fopen($this->filePath, 'c+');
        if (!$fp) {
            return false;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return false;
        }

        try {
            fseek($fp, 0);
            $content = stream_get_contents($fp);
            if ($content) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    $this->users = [];
                    foreach ($data as $userData) {
                        if (isset($userData['id'])) {
                            $this->users[$userData['id']] = User::fromArray($userData);
                        }
                    }
                }
            }

            if (!isset($this->users[$id])) {
                return false;
            }

            unset($this->users[$id]);

            $data = array_map(
                fn(User $u): array => $u->toArray(),
                $this->users,
            );

            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);

            return true;

        } catch (JsonException $e) {
            return false;
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getEmail()) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }
}