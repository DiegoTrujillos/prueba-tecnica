<?php
namespace App\Application\UseCase;

use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GetTrainerTeam
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

    public function execute(int $trainerId): ?array
    {
        $token = $this->tokenStorage->getToken();
        /** @var \App\Domain\Entity\User|null $currentUser */
        $currentUser = $token ? $token->getUser() : null;

        if (!$currentUser) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                'No autenticado.'
            );
        }

        if (!in_array('ROLE_PROFESSOR', $currentUser->getRoles()) && $currentUser->getId() !== $trainerId) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                'No tienes permiso para ver este equipo.'
            );
        }

        $trainer = $this->userRepository->findById($trainerId);
        if (!$trainer) {
            return null;
        }

        $team = $this->pokemonRepository->findByTrainer($trainer);

        $teamData = array_map(function($pokemon) {
            return [
                'id' => $pokemon->getId(),
                'name' => $pokemon->getName(),
                'nickname' => $pokemon->getNickname(),
                'types' => array_map(fn($t) => $t->getName(), $pokemon->getType()->toArray()),
                'level' => $pokemon->getLevel(),
                'health_points' => $pokemon->getHealthPoints(),
                'attack' => $pokemon->getAttack(),
                'defense' => $pokemon->getDefense(),
                'speed' => $pokemon->getSpeed(),
                'moves' => array_map(fn($m) => ['id' => $m->getId(), 'name' => $m->getName()], $pokemon->getMoves()->toArray())
            ];
        }, $team);

        return [
            'trainer' => [
                'id' => $trainer->getId(),
                'name' => $trainer->getUsername(),
                'type' => 'trainer'
            ],
            'team' => $teamData
        ];
    }
}