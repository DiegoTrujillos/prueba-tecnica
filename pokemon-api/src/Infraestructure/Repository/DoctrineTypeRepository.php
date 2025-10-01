<?php
namespace App\Infrastructure\Repository;

use App\Domain\Entity\Type;
use App\Domain\Repository\TypeRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineTypeRepository extends ServiceEntityRepository implements TypeRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function findById(int $id): ?Type
    {
        return $this->find($id);
    }

    public function findByName(string $name): ?Type
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Type $type): void
    {
        $this->_em->persist($type);
        $this->_em->flush();
    }
}