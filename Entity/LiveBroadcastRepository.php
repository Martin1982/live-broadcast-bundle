<?php

namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class LiveBroadcastRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function getPlannedBroadcasts()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->andX(
            $expr->lte('startTimestamp', new \DateTime()),
            $expr->gte('endTimestamp', new \DateTime())
        ));

        return $this->createQueryBuilder('lb')
            ->addCriteria($criteria)
            ->getQuery()
            ->getResult();
    }
}
