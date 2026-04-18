<?php

declare (strict_types = 1);

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Models\User;
use Faker\Factory as FakerFactory;

final class AddUserCommand implements CommandInterface
{
    private $faker;
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {
        $this->faker = FakerFactory::create('ru_RU');
    }

    public function execute(): void
    {

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = $this->faker->unique()->Email();

        $user = new User(0, $firstName, $lastName, $email);
        $this->repository->save($user);


        echo "✅ Пользователь добавлен:\n";
        echo "   ID: {$user->getId()}\n";
        echo "   Имя: {$firstName}\n";
        echo "   Фамилия: {$lastName}\n";
        echo "   Email: {$email}\n";
    }
}