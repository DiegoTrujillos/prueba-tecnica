<?php

namespace App\Security;

use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserProvider implements UserProviderInterface
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findByUsername($identifier);

        if (!$user) {
            throw new \Exception("Usuario no encontrado");
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof \App\Domain\Entity\User) {
            throw new UnsupportedUserException();
        }

        return $this->userRepository->findByUsername($user->getUsername());
    }

    public function supportsClass(string $class): bool
    {
        return $class === \App\Domain\Entity\User::class;
    }
}