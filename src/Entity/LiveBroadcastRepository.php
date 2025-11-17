<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

/**
 * Class LiveBroadcastRepository
 *
 * @codeCoverageIgnore
 */
class LiveBroadcastRepository extends EntityRepository
{
    /**
     * Get the planned broadcasts
     *
     * @return mixed
     *
     * @throws LiveBroadcastException
     */
    public function getPlannedBroadcasts(): mixed
    {
        $expr = Criteria::expr();

        if (!$expr) {
            return null;
        }

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
            throw new LiveBroadcastException(sprintf('Cannot query planned broadcasts: %s', $ex->getMessage()));
        }
    }
}
