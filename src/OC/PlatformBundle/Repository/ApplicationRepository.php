<?php
/**
 * Created by PhpStorm.
 * User: lionel
 * Date: 22.10.16
 * Time: 14:38
 */

namespace OC\PlatformBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ApplicationRepository extends EntityRepository
{
    public function getApplicationsWithAdvert($limit)
    {
        //Récupération du QueryBuilder
        $qb = $this->createQueryBuilder('a');

        //Paramétrage de la jointure
        $qb->innerJoin('a.advert', 'ad')->addSelect('ad');

        //limitation des réuslats
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();

    }
}