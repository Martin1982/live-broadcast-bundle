<?php declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\StreamOutput;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;

/**
 * Class OutputFacebook
 */
class OutputFacebook extends AbstractOutput implements DynamicStreamUrlInterface
{
    /**
     * @var ChannelFacebook
     */
    protected $channel;

    /**
     * @var FacebookApiService
     */
    protected $api;

    /**
     * @var LiveBroadcast|null
     */
    protected $broadcast;

    /**
     * OutputFacebook constructor
     *
     * @param FacebookApiService $api
     */
    public function __construct(FacebookApiService $api)
    {
        $this->api = $api;
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
        return sprintf('-c:v libx264 -crf 30 -preset ultrafast -c:a copy -f flv "%s"', $this->getStreamUrl());
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelType(): string
    {
        return ChannelFacebook::class;
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

        return $this->api->getStreamUrl($this->broadcast, $this->channel);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function setBroadcast(LiveBroadcast $broadcast): void
    {
        $this->broadcast = $broadcast;
    }
}
