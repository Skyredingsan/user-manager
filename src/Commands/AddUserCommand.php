<?php

declare(strict_types=1);

namespace UserManager\Commands;

use UserManager\Services\UserService;
use Faker\Factory;

final class AddUserCommand implements CommandInterface
{
    private $faker;

    public function __construct(
        private readonly UserService $service
    ) {
        $this->faker = Factory::create('ru_RU');
    }

    public function execute(): array
    {
        $user = $this->service->create([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => uniqid() . '@example.com'
        ]);

        return [
            'message' => 'User created',
            'user' => $user
        ];
    }
}