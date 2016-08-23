<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
     * @var GoogleRedirectService
     */
    private $redirectService;

    /**
     * YouTubePreBroadcastListener constructor.
     * @param YouTubeApiService $youTubeApiService
     * @param GoogleRedirectService $redirectService
     * @throws LiveBroadcastOutputException
     */
    public function __construct(YouTubeApiService $youTubeApiService, GoogleRedirectService $redirectService)
    {
        $this->youTubeApiService = $youTubeApiService;
        $this->redirectService = $redirectService;
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
            $redirectUri = $this->redirectService->getOAuthRedirectUrl();
            $this->youTubeApiService->initApiClients($redirectUri);

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
