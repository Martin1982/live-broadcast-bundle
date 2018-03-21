<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Metadata;

use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;

/**
 * Class StreamEventRepository
 *
 * @codeCoverageIgnore
 */
class StreamEventRepository extends EntityRepository
{
    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return null|Object|StreamEvent
     */
    public function findBroadcastingToChannel(LiveBroadcast $broadcast, AbstractChannel $channel)
    {
        return $this->findOneBy(compact('broadcast', 'channel'));
    }
}
