<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YoutubePreBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YoutubePreBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var YoutubeLiveService
     */
    private $youtubeLiveService;

    /**
     * YoutubePreBroadcastListener constructor.
     * @param YouTubeLiveService $youtubeLiveService
     */
    public function __construct(YouTubeLiveService $youtubeLiveService)
    {
        $this->youtubeLiveService = $youtubeLiveService;
    }

    /**
     * @param PreBroadcastEvent $event
     */
    public function onPreBroadcast(PreBroadcastEvent $event)
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputYoutube) {
            $this->youtubeLiveService->setupLivestream($liveBroadcast, $output->getChannel());

            $streamUrl = $this->youtubeLiveService->getStreamUrl();
            $output->setStreamUrl($streamUrl);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(PreBroadcastEvent::NAME => 'onPreBroadcast');
    }
}
