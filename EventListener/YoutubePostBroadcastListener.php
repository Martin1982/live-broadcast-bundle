<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YoutubePostBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YoutubePostBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var YoutubeLiveService
     */
    private $youtubeLiveService;

    /**
     * YoutubePostBroadcastListener constructor.
     * @param YouTubeLiveService $youtubeLiveService
     */
    public function __construct(YouTubeLiveService $youtubeLiveService)
    {
        $this->youtubeLiveService = $youtubeLiveService;
    }

    /**
     * @param PostBroadcastEvent $event
     */
    public function onPostBroadcast(PostBroadcastEvent $event)
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

//        if ($output instanceof OutputYoutube) {
//            $this->youtubeLiveService->transitionState($liveBroadcast, $output->getChannel());
//        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(PostBroadcastEvent::NAME => 'onPostBroadcast');
    }
}
