<?php

namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputUstream
 * @package Martin1982\LiveBroadcastBundle\Service\StreamOutput
 */
class OutputUstream implements OutputInterface
{
    /**
     * @var ChannelUstream
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
        if ((!($this->channel instanceof ChannelUstream)) ||
            empty($this->channel->getStreamKey()) ||
            empty($this->channel->getStreamServer())) {
            throw new LiveBroadcastOutputException(__FUNCTION__.' Ustream channel not configured');
        }

        return sprintf(
            '-vcodec copy -acodec copy -f flv "rtmp://%s/%s"',
            str_replace('rtmp://', '', $this->channel->getStreamServer()),
            $this->channel->getStreamKey()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType()
    {
        return ChannelUstream::class;
    }
}
