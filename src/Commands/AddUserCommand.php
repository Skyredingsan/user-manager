<?php

namespace UserManager\Commands;

use UserManager\Repositories\UserRepositoryInterface;
use UserManager\Models\User;
use Faker\Factory as FakerFactory;

class AddUserCommand implements CommandInterface
{
    private UserRepositoryInterface $repository;
    private $faker;


    public function __construct(UserRepositoryInterface $repository) {
        $this->repository = $repository;
        $this->faker = FakerFactory::create('ru_RU');
    }

    public function execute(): void
    {
        $id = $this->repository->getNextId();

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = $this->faker->unique()->Email();

        $user = new User($id, $firstName, $lastName, $email);
        $this->repository->save($user);


        echo "✅ Пользователь добавлен:\n";
        echo "   ID: {$id}\n";
        echo "   Имя: {$firstName}\n";
        echo "   Фамилия: {$lastName}\n";
        echo "   Email: {$email}\n";
    }
}