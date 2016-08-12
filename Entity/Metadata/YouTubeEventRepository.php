<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class YouTubeEventRepository
 * @package Martin1982\LiveBroadcastBundle\Entity\Metadata
 */
class YouTubeEventRepository extends EntityRepository
{
    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYouTube $channel
     * @return null|YouTubeEvent
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
     */
    public function getTestableEvents()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->andX(
            $expr->lt('event.lastKnownState', YouTubeEvent::STATE_LOCAL_TESTING),
            $expr->gte('broadcast.endTimestamp', new \DateTime())
        ));

        return $this->createQueryBuilder('event')
            ->leftJoin(
                'Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast',
                'broadcast',
                'event.broadcast = broadcast.broadcast'
            )
            ->addCriteria($criteria)
            ->getQuery()
            ->getResult();
    }
}
