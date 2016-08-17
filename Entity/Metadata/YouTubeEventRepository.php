<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\QueryException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class YouTubeEventRepository
 * @package Martin1982\LiveBroadcastBundle\Entity\Metadata
 */
class YouTubeEventRepository extends EntityRepository
{
    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYouTube $channel
     * @return null|object|YouTubeEvent
     */
    public function findBroadcastingToChannel(LiveBroadcast $broadcast, ChannelYouTube $channel)
    {
        return $this->findOneBy(array(
            'broadcast' => $broadcast,
            'channel'   => $channel,
        ));
    }

    /**
     * @return YouTubeEvent[]
     * @throws LiveBroadcastOutputException
     */
    public function getTestableEvents()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->andX(
            $expr->lt('event.lastKnownState', YouTubeEvent::STATE_LOCAL_TESTING),
            $expr->gte('broadcast.endTimestamp', new \DateTime())
        ));

        try {
            return $this->createQueryBuilder('event')
                ->leftJoin('event.broadcast', 'broadcast')
                ->addCriteria($criteria)
                ->getQuery()
                ->getResult();
        } catch (QueryException $ex) {
            throw new LiveBroadcastOutputException('Cannot query testable events: '.$ex->getMessage());
        }
    }
}
