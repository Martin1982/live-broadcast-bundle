<?php declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputUstream
 */
class OutputUstream extends AbstractOutput
{
    /**
     * @var ChannelUstream
     */
    protected $channel;

    /**
     * Get the output parameters for streaming.
     *
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function generateOutputCmd(): string
    {
        if ((!($this->channel instanceof ChannelUstream)) ||
            null === $this->channel->getStreamKey() ||
            null === $this->channel->getStreamServer()) {
            throw new LiveBroadcastOutputException(sprintf('%s Ustream channel not configured', __FUNCTION__));
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
    public function getChannelType(): string
    {
        return ChannelUstream::class;
    }
}
