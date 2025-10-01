<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Pokemon;
use App\Domain\Repository\PokemonRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePokemonRepository extends ServiceEntityRepository implements PokemonRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function findById(int $id): ?Pokemon
    {
        return $this->find($id);
    }

    public function findOneWild(): ?Pokemon
    {
        return $this->createQueryBuilder('p')
            ->where('p.trainer IS NULL')
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Pokemon $pokemon): void
    {
        $this->_em->persist($pokemon);
        $this->_em->flush();
    }
}
