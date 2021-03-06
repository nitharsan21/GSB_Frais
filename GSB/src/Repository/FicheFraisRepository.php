<?php

namespace App\Repository;

use App\Entity\FicheFrais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FicheFrais|null find($id, $lockMode = null, $lockVersion = null)
 * @method FicheFrais|null findOneBy(array $criteria, array $orderBy = null)
 * @method FicheFrais[]    findAll()
 * @method FicheFrais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FicheFraisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FicheFrais::class);
    }

    // /**
    //  * @return FicheFrais[] Returns an array of FicheFrais objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FicheFrais
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    
    public function ficheforfaitwithMonthandIdv($mois , $idvisiteur): FicheFrais
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('f')
            ->from(FicheFrais::class, 'f')
            ->where('f.idVisiteur = :id')
            ->andWhere('f.mois = :mois')
            ->setParameter('id',$idvisiteur)
            ->setParameter('mois',$mois);
             
        $result =  $queryBuilder->getQuery()->getOneOrNullResult();
           
        return $result;
    }
    
    
     public function moisparVisiteur($idVisiteur)
    {
        $queryBuilder = $this->_em->createQueryBuilder()
            ->select('l')
            ->from(FicheFrais::class, 'l')
            ->where('l.idVisiteur = :id')
            ->groupBy('l.mois')
            ->setParameter('id',$idVisiteur);
            

           $result =  $queryBuilder->getQuery()->getResult();
           
           return $result;

    }
    
    
    
}
