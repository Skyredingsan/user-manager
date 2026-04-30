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

    public function execute(): string
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = $this->faker->unique()->email();

        $user = $this->service->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email
        ]);

        return sprintf(
            "User added: ID=%d, %s %s, %s",
            $user->getId(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getEmail()
        );
    }
}