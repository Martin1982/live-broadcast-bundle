<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;

/**
 * Class ChannelApiStack
 */
class ChannelApiStack
{
    /**
     * @var ChannelApiInterface[]
     */
    private $apis = [];

    /**
     * @param ChannelApiInterface $api
     */
    public function addApi(ChannelApiInterface $api): void
    {
        $this->apis[] = $api;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return ChannelApiInterface|null
     */
    public function getApiForChannel(AbstractChannel $channel): ?ChannelApiInterface
    {
        $channelClass = \get_class($channel);
        $service = null;

        switch ($channelClass) {
            case ChannelYouTube::class:
                $service = $this->findServiceObject(YouTubeApiService::class);
                break;
            case ChannelFacebook::class:
                $service = $this->findServiceObject(FacebookApiService::class);
                break;

            default:
                break;
        }

        return $service;
    }

    /**
     * @param string $className
     *
     * @return ChannelApiInterface|null
     */
    protected function findServiceObject($className): ?ChannelApiInterface
    {
        $serviceApi = null;

        foreach ($this->apis as $api) {
            if ($api instanceof $className) {
                $serviceApi = $api;
            }
        }

        return $serviceApi;
    }
}
