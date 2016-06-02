<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelFacebook
 * @package Martin1982\LiveBroadcastBundle\Entity\Channel
 *
 * @ORM\Table(name="channel_facebook", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelFacebook extends BaseChannel
{
    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=128, nullable=false)
     */
    protected $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_entity_id", type="string", length=128, nullable=false)
     */
    protected $fbEntityId;
}
