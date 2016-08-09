<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;

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
     * @param Router $router
     * @param string $redirectRoute
     */
    public function __construct(YouTubeLiveService $youtubeLiveService, Router $router, $redirectRoute)
    {
        $this->youtubeLiveService = $youtubeLiveService;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youtubeLiveService->initApiClients($redirectUri);
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
            $streamUrl = $this->youtubeLiveService->getStreamUrl($liveBroadcast, $output->getChannel());
            if ($streamUrl) {
                $output->setStreamUrl($streamUrl);
            }
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
