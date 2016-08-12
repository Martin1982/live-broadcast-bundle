<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YouTubePostBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YouTubePostBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var YouTubeApiService
     */
    private $youTubeApiService;

    /**
     * YouTubePostBroadcastListener constructor.
     * @param YouTubeApiService $youTubeApiService
     * @param Router $router
     * @param string $redirectRoute
     */
    public function __construct(YouTubeApiService $youTubeApiService, Router $router, $redirectRoute)
    {
        $this->youTubeApiService = $youTubeApiService;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youTubeApiService->initApiClients($redirectUri);
    }

    /**
     * @param PostBroadcastEvent $event
     */
    public function onPostBroadcast(PostBroadcastEvent $event)
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputYouTube) {
            $channel = $output->getChannel();
            $this->youTubeApiService->transitionState($liveBroadcast, $channel, YouTubeEvent::STATE_REMOTE_LIVE);
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
