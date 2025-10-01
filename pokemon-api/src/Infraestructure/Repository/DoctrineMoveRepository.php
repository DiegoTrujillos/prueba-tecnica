<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Move;
use App\Domain\Repository\MoveRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineMoveRepository extends ServiceEntityRepository implements MoveRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Move::class);
    }

    public function save(Move $move): void
    {
        $this->_em->persist($move);
        $this->_em->flush();
    }

    public function findById(int $id): ?Move
    {
        return $this->find($id);
    }
}