<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\StreamEndEvent;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YouTubeStreamEndListener
 */
class YouTubeStreamEndListener implements EventSubscriberInterface
{
    /**
     * @var YouTubeApiService
     */
    protected $youTubeApi;

    /**
     * YouTubeStreamEndListener constructor
     *
     * @param YouTubeApiService $youTubeApi
     */
    public function __construct(YouTubeApiService $youTubeApi)
    {
        $this->youTubeApi = $youTubeApi;
    }

    /**
     * @param StreamEndEvent $event
     */
    public function onStreamEnd(StreamEndEvent $event)
    {
        $broadcast = $event->getBroadcast();
        $channel = $event->getChannel();

        if ($channel instanceof ChannelYouTube) {
            $this->youTubeApi->transitionState($broadcast, $channel, YouTubeEvent::STATE_REMOTE_COMPLETE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StreamEndEvent::NAME => 'onStreamEnd'];
    }
}
