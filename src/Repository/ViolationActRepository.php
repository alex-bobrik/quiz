<?php

namespace App\Repository;

use App\Entity\ViolationAct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ViolationAct|null find($id, $lockMode = null, $lockVersion = null)
 * @method ViolationAct|null findOneBy(array $criteria, array $orderBy = null)
 * @method ViolationAct[]    findAll()
 * @method ViolationAct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViolationActRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViolationAct::class);
    }

    // /**
    //  * @return ViolationAct[] Returns an array of ViolationAct objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ViolationAct
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
