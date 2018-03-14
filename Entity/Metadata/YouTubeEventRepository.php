<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\QueryException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class YouTubeEventRepository
 *
 * @codeCoverageIgnore
 */
class YouTubeEventRepository extends EntityRepository
{
    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return null|Object|YouTubeEvent
     */
    public function findBroadcastingToChannel(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        return $this->findOneBy(compact('broadcast', 'channel'));
    }

    /**
     * @return YouTubeEvent[]
     *
     * @throws LiveBroadcastOutputException
     */
    public function getTestableEvents(): ?array
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $now = new \DateTime();

        $criteria->where($expr->andX(
            $expr->lt('event.lastKnownState', YouTubeEvent::STATE_LOCAL_TESTING),
            $expr->gte('broadcast.endTimestamp', new \DateTime()),
            $expr->lte('broadcast.startTimestamp', $now->add(\DateInterval::createFromDateString('30 minutes')))
        ));

        try {
            return $this->createQueryBuilder('event')
                ->leftJoin('event.broadcast', 'broadcast')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } catch (QueryException $ex) {
            throw new LiveBroadcastOutputException(sprintf('Cannot query testable events: %s', $ex->getMessage()));
        }
    }
}
