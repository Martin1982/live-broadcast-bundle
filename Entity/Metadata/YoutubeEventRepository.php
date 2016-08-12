<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class YoutubeEventRepository
 * @package Martin1982\LiveBroadcastBundle\Entity\Metadata
 */
class YoutubeEventRepository extends EntityRepository
{
    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYoutube $channel
     * @return null|object
     */
    public function findBroadcastingToChannel(LiveBroadcast $broadcast, ChannelYoutube $channel)
    {
        return $this->findOneBy(array(
            'broadcast' => $broadcast,
            'channel'   => $channel,
        ));
    }

    /**
     * @return YoutubeEvent[]
     */
    public function getTestableEvents()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->andX(
            $expr->lt('event.lastKnownState', YoutubeEvent::STATE_LOCAL_TESTING),
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
