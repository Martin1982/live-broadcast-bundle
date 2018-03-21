<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FacebookPreBroadcastListener
 */
class FacebookPreBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var FacebookApiService
     */
    private $facebookApiService;

    /**
     * FacebookPreBroadcastListener constructor.
     * @param FacebookApiService $facebookApiService
     */
    public function __construct(FacebookApiService $facebookApiService)
    {
        $this->facebookApiService = $facebookApiService;
    }

    /**
     * @param PreBroadcastEvent $event
     *
     * @throws \InvalidArgumentException
     */
    public function onPreBroadcast(PreBroadcastEvent $event): void
    {
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof OutputFacebook) {
            $streamUrl = $this->facebookApiService->createFacebookLiveVideo($liveBroadcast, $output);
            $output->setStreamUrl($streamUrl);
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
