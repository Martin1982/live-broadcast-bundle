<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YouTubePreBroadcastListener
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
     * YouTubePreBroadcastListener constructor
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
     * @param PreBroadcastEvent $event
     *
     * @throws LiveBroadcastOutputException
     */
    public function onPreBroadcast(PreBroadcastEvent $event): void
    {
        /** @var LiveBroadcast $liveBroadcast */
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputYouTube) {
            $redirectUri = $this->redirectService->getOAuthRedirectUrl();
            $this->youTubeApiService->initApiClients($redirectUri);

            $stream = $this->youTubeApiService->getStream($liveBroadcast, $output->getChannel());
            if ($stream) {
                $streamUrl = $this->youTubeApiService->getStreamUrl($stream);
                if ($streamUrl) {
                    $output->setStreamUrl($streamUrl);
                }
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [PreBroadcastEvent::NAME => 'onPreBroadcast'];
    }
}
