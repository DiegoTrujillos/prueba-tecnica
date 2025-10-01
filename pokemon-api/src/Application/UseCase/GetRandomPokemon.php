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
            'level' => $pokemon->getLevel(),
            'types' => array_map(fn($t)=> $t->getName(), $pokemon->getType()->toArray())
        ];
    }
}
