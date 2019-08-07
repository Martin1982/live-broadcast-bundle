<?php declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;

/**
 * Class OutputYouTube
 */
class OutputYouTube extends AbstractOutput implements DynamicStreamUrlInterface
{
    /**
     * @var ChannelYouTube
     */
    protected $channel;

    /**
     * @var YouTubeApiService
     */
    protected $api;

    /**
     * @var LiveBroadcast|null
     */
    protected $broadcast;

    /**
     * OutputYouTube constructor
     *
     * @param YouTubeApiService $api
     */
    public function __construct(YouTubeApiService $api)
    {
        $this->api = $api;
    }

    /**
     * @return ChannelYouTube
     */
    public function getChannel(): ChannelYouTube
    {
        return $this->channel;
    }

    /**
     * Give the cmd string to start the stream.
     *
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function generateOutputCmd(): string
    {
        $params = '-vf scale=-1:720 -c:v libx264 -pix_fmt yuv420p ';
        $params .= '-preset veryfast -r 30 -g 60 -b:v 4000k -c:a aac -f flv "%s"';

        return sprintf($params, $this->getStreamUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType(): string
    {
        return ChannelYouTube::class;
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function setBroadcast(LiveBroadcast $broadcast): void
    {
        $this->broadcast = $broadcast;
    }

    /**
     * @return string
     *
     * @throws LiveBroadcastOutputException
     */
    public function getStreamUrl(): string
    {
        if (!$this->broadcast) {
            throw new LiveBroadcastOutputException('No broadcast set');
        }

        if (!$this->channel) {
            throw new LiveBroadcastOutputException('No channel set');
        }

        return (string) $this->api->getStreamUrl($this->broadcast, $this->channel);
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
        return $this->api->canStream($this->channel);
    }
}
