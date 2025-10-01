<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Type;

interface TypeRepositoryInterface
{
    public function findById(int $id): ?Type;
    public function findByName(string $name): ?Type;
    public function save(Type $type): void;
}