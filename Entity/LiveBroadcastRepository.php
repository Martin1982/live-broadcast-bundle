<?php

namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class LiveBroadcastRepository
 * @package Martin1982\LiveBroadcastBundle\Entity
 */
class LiveBroadcastRepository extends EntityRepository
{
    /**
     * Get the planned broadcasts
     *
     * @return array
     * @throws LiveBroadcastException
     */
    public function getPlannedBroadcasts()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->andX(
            $expr->lte('startTimestamp', new \DateTime()),
            $expr->gte('endTimestamp', new \DateTime())
        ));

        try {
            return $this->createQueryBuilder('lb')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } catch (QueryException $ex) {
            throw new LiveBroadcastException('Cannot query planned broadcasts: '.$ex->getMessage());
        }
    }
}
