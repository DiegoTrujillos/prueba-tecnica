<?php

namespace App\Application\UseCase;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUser
{
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserRepositoryInterface $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public function execute($username, $password, $roles): User
    {
        if (!is_string($username) || trim($username) === '') {
            throw new \InvalidArgumentException('El nombre de usuario debe ser una cadena no vacía.');
        }

        if (!is_string($password) || trim($password) === '') {
            throw new \InvalidArgumentException('La contraseña debe ser una cadena no vacía.');
        }

        if (!is_array($roles) || empty($roles)) {
            throw new \InvalidArgumentException('Los roles deben ser un array no vacío.');
        }

        foreach ($roles as $role) {
            if (!is_string($role) || trim($role) === '') {
                throw new \InvalidArgumentException('Cada rol debe ser una cadena no vacía.');
            }
        }

        $user = new User();
        $user->setUsername($username);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->save($user);

        return $user;
    }
}