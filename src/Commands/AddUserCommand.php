<?php

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Models\User;

class AddUserCommand implements CommandInterface
{
    private UserRepositoryInterface $repository;

    private array $firstNames = ['Иван', 'Петр', 'Алексей', 'Михаил', 'Сергей', 'Анна', 'Мария', 'Екатерина'];
    private array $lastNames = ['Иванов', 'Петров', 'Кузнецов', 'Смирнов'];
    private array $domains = ['example.com', 'test.com', 'mail.ru', 'gmail.com'];

    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function execute(): void
    {
        $id = $this->repository->getNextId();
        $firstName = $this->generateFirstName();
        $lastName = $this->generateSecondName();
        $email = $this->generateEmail($firstName, $lastName);

        $user = new User($id, $firstName, $lastName, $email);
        $this->repository->save($user);

        print_r("Пользователь добавлен: ID=$id, $firstName, $lastName, $email\n");
    }

    private function generateFirstName(): string
    {
        return $this->firstNames[array_rand($this->firstNames)];
    }

    private function generateSecondName(): string
    {
        return $this->lastNames[array_rand($this->lastNames)];
    }

    private function generateEmail(string $firstName, string $lastName): string
    {
        $domain = $this->domains[array_rand($this->domains)];
        $translit = $this->cyrillicToLatin($firstName . '.' . $lastName);
        return strtolower($translit) . '@' . $domain;
    }

    private function cyrillicToLatin(string $text): string {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        ];

        $text = mb_strtolower($text);
        return strtr($text, $map);
    }
}