<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
     * @param PostBroadcastEvent $event
     */
    public function onPostBroadcast(PostBroadcastEvent $event)
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputYoutube) {
            $channel = $output->getChannel();
            $this->youtubeLiveService->transitionState($liveBroadcast, $channel, YoutubeEvent::STATE_REMOTE_LIVE);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(PostBroadcastEvent::NAME => 'onPostBroadcast');
    }
}
