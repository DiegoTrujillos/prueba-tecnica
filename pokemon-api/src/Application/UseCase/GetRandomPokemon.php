<?php
namespace App\Application\UseCase;

use App\Domain\Repository\PokemonRepositoryInterface;

class GetRandomPokemon
{
    private PokemonRepositoryInterface $pokemonRepository;

    public function __construct(PokemonRepositoryInterface $pokemonRepository)
    {
        $this->pokemonRepository = $pokemonRepository;
    }

    public function execute(): ?array
    {
        $pokemon = $this->pokemonRepository->findOneWild();
        if (!$pokemon) {
            return null;
        }

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
            'catch_rate' => $pokemon->getCatchRate(),
            'moves' => array_map(fn($m) => [
                'id' => $m->getId(),
                'name' => $m->getName()
            ], $pokemon->getMove()->toArray())
        ];
    }
}