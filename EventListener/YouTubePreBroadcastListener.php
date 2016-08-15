<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class YouTubePreBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YouTubePreBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var YouTubeApiService
     */
    private $youTubeApiService;

    /**
     * YouTubePreBroadcastListener constructor.
     * @param YouTubeApiService $youTubeApiService
     * @param RouterInterface $router
     * @param string $redirectRoute
     * @throws \Exception
     */
    public function __construct(YouTubeApiService $youTubeApiService, RouterInterface $router, $redirectRoute)
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
     * @param PreBroadcastEvent $event
     */
    public function onPreBroadcast(PreBroadcastEvent $event)
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputYouTube) {
            $streamUrl = $this->youTubeApiService->getStreamUrl($liveBroadcast, $output->getChannel());
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
