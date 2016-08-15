<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;

/**
 * Interface OutputInterface
 * @package Martin1982\LiveBroadcastBundle\Service\StreamOutput
 */
interface OutputInterface
{
    /**
     * @param BaseChannel $channel
     * @return OutputInterface
     */
    public function setChannel(BaseChannel $channel);

    /**
     * Give the cmd string to start the stream.
     *
     * @return string
     */
    public function generateOutputCmd();

    /**
     * Returns the channel type
     *
     * @return BaseChannel
     */
    public function getChannelType();
}
