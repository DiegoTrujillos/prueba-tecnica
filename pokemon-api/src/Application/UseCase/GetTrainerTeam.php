<?php
namespace App\Application\UseCase;

use App\Domain\Repository\PokemonRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

    public function execute(mixed $trainerId): ?array
    {
        if (!is_numeric($trainerId)) {
            throw new \InvalidArgumentException('El ID del entrenador debe ser un número.');
        }
        $trainerId = (int)$trainerId;

        $token = $this->tokenStorage->getToken();
        /** @var \App\Domain\Entity\User|null $currentUser */
        $currentUser = $token ? $token->getUser() : null;

        if (!$currentUser instanceof \App\Domain\Entity\User) {
            throw new AccessDeniedHttpException('No autenticado.');
        }

        if (!in_array('ROLE_PROFESSOR', $currentUser->getRoles()) && $currentUser->getId() !== $trainerId) {
            throw new AccessDeniedHttpException('No tienes permiso para ver este equipo.');
        }

        $trainer = $this->userRepository->findById($trainerId);
        if (!$trainer) {
            return null;
        }

        $team = $this->pokemonRepository->findByTrainer($trainer);

        $teamData = array_map(function($pokemon) {
            $types = [];
            foreach ($pokemon->getTypes() ?? [] as $type) {
                if (method_exists($type, 'getName')) {
                    $types[] = $type->getName();
                }
            }

            $moves = [];
            foreach ($pokemon->getMoves() ?? [] as $move) {
                if (method_exists($move, 'getId') && method_exists($move, 'getName')) {
                    $moves[] = ['id' => $move->getId(), 'name' => $move->getName()];
                }
            }

            return [
                'id' => $pokemon->getId(),
                'name' => $pokemon->getName(),
                'nickname' => $pokemon->getNickname(),
                'types' => $types,
                'level' => $pokemon->getLevel(),
                'health_points' => $pokemon->getHealthPoints(),
                'attack' => $pokemon->getAttack(),
                'defense' => $pokemon->getDefense(),
                'speed' => $pokemon->getSpeed(),
                'moves' => $moves
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