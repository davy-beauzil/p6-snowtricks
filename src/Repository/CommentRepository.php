<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function add(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->persist($entity)
        ;

        if ($flush) {
            $this->getEntityManager()
                ->flush()
            ;
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()
            ->remove($entity)
        ;

        if ($flush) {
            $this->getEntityManager()
                ->flush()
            ;
        }
    }

    /**
     * @return Comment[]
     */
    public function getCommentWithPage(Trick $trick, int $page = 1, int $count = 5): array
    {
        /** @var array $comments */
        $comments = $this->createQueryBuilder('c')
            ->andWhere('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->setFirstResult(($page - 1) * $count)
            ->setMaxResults($count)
            ->orderBy('c.created_at', Criteria::DESC)
            ->getQuery()
            ->getResult()
        ;

        foreach ($comments as $key => $comment) {
            if (! $comment instanceof Comment) {
                unset($comments[$key]);
            }
        }

        return $comments;
    }

    public function countPages(Trick $trick, int $count = 5): int
    {
        /** @var int $totalComments */
        $totalComments = $this->createQueryBuilder('c')
            ->select('count(c)')
            ->andWhere('c.trick = :trick')
            ->setParameter('trick', $trick)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) (ceil($totalComments / $count));
    }
}
