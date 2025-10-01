<?php
namespace App\Application\UseCase;

use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Domain\Entity\User;

class AssignPokemonToTrainer
{
    private PokemonRepositoryInterface $pokemonRepository;
    private UserRepositoryInterface $userRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        PokemonRepositoryInterface $pokemonRepository,
        UserRepositoryInterface $userRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->pokemonRepository = $pokemonRepository;
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public function execute(int $trainerId, int $pokemonId): void
    {
        $token = $this->tokenStorage->getToken();
        /** @var User|null $currentUser */
        $currentUser = $token ? $token->getUser() : null;

        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Usuario no autenticado.');
        }

        if (!in_array('ROLE_PROFESSOR', $currentUser->getRoles()) && $currentUser->getId() !== $trainerId) {
            throw new AccessDeniedHttpException('No tienes permiso para asignar Pokémon a este entrenador.');
        }

        $trainer = $this->userRepository->findById($trainerId);
        if (!$trainer) {
            throw new NotFoundHttpException('Entrenador no encontrado.');
        }

        $pokemon = $this->pokemonRepository->findById($pokemonId);
        if (!$pokemon || $pokemon->getTrainer() !== null) {
            throw new NotFoundHttpException('Pokémon no disponible.');
        }

        $pokemon->setTrainer($trainer);
        $this->pokemonRepository->save($pokemon);
    }
}