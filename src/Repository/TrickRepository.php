<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function add(Trick $entity, bool $flush = false): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Trick $entity, bool $flush = false): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function trickExists(string $slug, string $idToAvoid = null): bool
    {
        try {
            $qb = $this->createQueryBuilder('t')
                ->select('count(t.slug)')
                ->where('t.slug = :slug')
                ->setParameter('slug', $slug)
            ;

            if ($idToAvoid !== null) {
                $qb->andWhere('t.id != :id')
                    ->setParameter('id', $idToAvoid)
                ;
            }

            /** @var int $count */
            $count = $qb->getQuery()
                ->getSingleScalarResult()
            ;

            return $count !== 0;
        } catch (NoResultException|NonUniqueResultException) {
            return true;
        }
    }
}
