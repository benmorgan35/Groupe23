<?php
namespace AppBundle\Repository;

/**
 * TaxrefRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaxrefRepository extends \Doctrine\ORM\EntityRepository
{

    public function getBirdsWithObservation()
    {
        return $this->createQueryBuilder('tax')
            ->addSelect('to')
            ->leftJoin('tax.observations', 'to')
            ->where('to.valided = true')
            ->getQuery()
            ->getResult();
    }

    public function getBirdsWithObservationPublic()
    {
        return $this->createQueryBuilder('tax')
            ->leftJoin('tax.observations', 'to')
            ->addSelect('to')
            ->where('to.visible = true')
            ->getQuery()
            ->getResult();
    }

}
