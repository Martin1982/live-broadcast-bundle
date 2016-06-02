<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelTwitch
 * @package Martin1982\LiveBroadcastBundle\Entity\Channel
 *
 * @ORM\Table(name="channel_twitch", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelTwitch extends BaseChannel
{
    /**
     * @var string
     *
     * @ORM\Column(name="stream_key", type="string", length=128, nullable=false)
     */
    protected $streamKey;
}
