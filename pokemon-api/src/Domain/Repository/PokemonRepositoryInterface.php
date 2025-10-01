<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Pokemon;

interface PokemonRepositoryInterface
{
    public function findById(int $id): ?Pokemon;
    public function findOneWild(): ?Pokemon;
    public function save(Pokemon $pokemon): void;
}