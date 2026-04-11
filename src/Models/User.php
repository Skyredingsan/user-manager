<?php

declare(strict_types=1);

namespace UserManager\Models;

final readonly class User {

    public function __construct(
        private int $id,
        private string $firstName,
        private string $lastName,
        private string $email,
    ){}

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return array{id: int, firstName: string, lastName: string, email: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email
        ];
    }

    /**
     * @param array{id: int, firstName: string, lastName: string, email: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['firstName'],
            $data['lastName'],
            $data['email']
        );
    }





}
