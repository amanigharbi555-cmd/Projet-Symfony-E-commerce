<?php

namespace App\Repository;

use App\Entity\Coupons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Coupons|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coupons|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coupons[]    findAll()
 * @method Coupons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CouponsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coupons::class);
    }

    /**
     * Trouve tous les coupons actifs et valides
     * 
     * @return Coupons[] Returns an array of Coupons objects
     */
    public function findActiveCoupons(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.is_valid = :valid')
            ->andWhere('c.validity > :now')
            ->setParameter('valid', true)
            ->setParameter('now', $now)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un coupon par son code (insensible à la casse)
     * 
     * @param string $code Le code du coupon
     * @return Coupons|null
     */
    public function findByCode(string $code): ?Coupons
    {
        return $this->createQueryBuilder('c')
            ->andWhere('UPPER(c.code) = :code')
            ->setParameter('code', strtoupper($code))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les coupons qui expirent bientôt (dans les X jours)
     * 
     * @param int $days Nombre de jours
     * @return Coupons[] Returns an array of Coupons objects
     */
    public function findExpiringSoon(int $days = 7): array
    {
        $now = new \DateTime();
        $futureDate = new \DateTime("+{$days} days");
        
        return $this->createQueryBuilder('c')
            ->andWhere('c.is_valid = :valid')
            ->andWhere('c.validity BETWEEN :now AND :future')
            ->setParameter('valid', true)
            ->setParameter('now', $now)
            ->setParameter('future', $futureDate)
            ->orderBy('c.validity', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les coupons par type
     * 
     * @param int $typeId L'ID du type de coupon
     * @return Coupons[] Returns an array of Coupons objects
     */
    public function findByType(int $typeId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.coupons_types = :type')
            ->setParameter('type', $typeId)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les coupons les plus utilisés
     * 
     * @param int $limit Nombre de résultats
     * @return Coupons[] Returns an array of Coupons objects
     */
    public function findMostUsed(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.orders', 'o')
            ->groupBy('c.id')
            ->orderBy('COUNT(o.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
