<?php

namespace App\Repository;

use App\Entity\LigneFraisForfait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LigneFraisForfait|null find($id, $lockMode = null, $lockVersion = null)
 * @method LigneFraisForfait|null findOneBy(array $criteria, array $orderBy = null)
 * @method LigneFraisForfait[]    findAll()
 * @method LigneFraisForfait[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LigneFraisForfaitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneFraisForfait::class);
    }

    // /**
    //  * @return LigneFraisForfait[] Returns an array of LigneFraisForfait objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LigneFraisForfait
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /*
    public function moisparVisiteur($idVisiteur)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('mois')
            ->from(LigneFraisForfait::class, 'l')
            ->where('l.idVisiteur = :id')
            ->groupBy('l.mois')
            ->setParameter('id',$idVisiteur);
            

           $result =  $queryBuilder->getQuery();
           
           return $result;

    }
     * 
     */
    
    public function getLFFwithIDVisiteurAndMonth($idVisiteur,$mois)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('l')
            ->from(LigneFraisForfait::class, 'l')
            ->where('l.idVisiteur = :id')
            ->andWhere('l.mois = :mois')
            ->setParameter('id',$idVisiteur)
            ->setParameter('mois',$mois);
             
        $result =  $queryBuilder->getQuery()->getResult();
           
        return $result;
        
        
    }
    
   
    
}