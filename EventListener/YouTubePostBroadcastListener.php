<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
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
     * @var GoogleRedirectService
     */
    private $redirectService;

    /**
     * YouTubePostBroadcastListener constructor.
     * @param YouTubeApiService $youTubeApiService
     * @param GoogleRedirectService $redirectService
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function __construct(YouTubeApiService $youTubeApiService, GoogleRedirectService $redirectService)
    {
        $this->youTubeApiService = $youTubeApiService;
        $this->redirectService = $redirectService;
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
            $redirectUri = $this->redirectService->getOAuthRedirectUrl();
            $this->youTubeApiService->initApiClients($redirectUri);

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
