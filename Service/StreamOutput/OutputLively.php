<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputLively
 * @package Martin1982\LiveBroadcastBundle\Service\StreamOutput
 */
class OutputLively implements OutputInterface
{
    /**
     * @var ChannelLively
     */
    protected $channel;

    /**
     * {@inheritdoc}
     */
    public function setChannel(BaseChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get the output parameters for streaming.
     *
     * @return string
     * @throws LiveBroadcastOutputException
     */
    public function generateOutputCmd()
    {
        if ((!($this->channel instanceof ChannelLively)) ||
            empty($this->channel->getStreamKey()) ||
            empty($this->channel->getStreamServer())) {
            throw new LiveBroadcastOutputException(__FUNCTION__.' Live.ly channel not configured');
        }

        return sprintf(
            '-vcodec copy -acodec copy -f flv "rtmp://%s/%s"',
            $this->channel->getStreamServer(),
            $this->channel->getStreamKey()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelLively::class;
    }
}
