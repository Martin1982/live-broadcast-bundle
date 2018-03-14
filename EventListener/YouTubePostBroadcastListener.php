<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\PostBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YouTubePostBroadcastListener
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
     * YouTubePostBroadcastListener constructor
     *
     * @param YouTubeApiService     $youTubeApiService
     * @param GoogleRedirectService $redirectService
     */
    public function __construct(YouTubeApiService $youTubeApiService, GoogleRedirectService $redirectService)
    {
        $this->youTubeApiService = $youTubeApiService;
        $this->redirectService = $redirectService;
    }

    /**
     * @param PostBroadcastEvent $event
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function onPostBroadcast(PostBroadcastEvent $event): void
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
    public static function getSubscribedEvents(): array
    {
        return [PostBroadcastEvent::NAME => 'onPostBroadcast'];
    }
}
