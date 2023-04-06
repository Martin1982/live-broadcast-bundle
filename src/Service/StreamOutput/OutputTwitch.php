<?php declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;

/**
 * Class OutputTwitch
 */
class OutputTwitch extends AbstractOutput
{
    /**
     * @var ChannelTwitch|AbstractChannel|null
     */
    protected ?AbstractChannel $channel = null;

    /**
     * Get the output parameters for streaming.
     *
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function generateOutputCmd(): string
    {
        $this->validate();

        return sprintf(
            '-vcodec copy -acodec copy -f flv "rtmp://%s/app/%s"',
            str_replace('rtmp://', '', $this->channel->getStreamServer()),
            $this->channel->getStreamKey()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType(): string
    {
        return ChannelTwitch::class;
    }

    /**
     * Validate channel usage
     *
     * @return bool
     *
     * @throws LiveBroadcastOutputException
     */
    public function validate(): bool
    {
        if ((!($this->channel instanceof ChannelTwitch)) ||
            null === $this->channel->getStreamKey() ||
            null === $this->channel->getStreamServer()) {
            throw new LiveBroadcastOutputException(sprintf('%s Twitch channel not configured', __FUNCTION__));
        }

        return true;
    }
}
