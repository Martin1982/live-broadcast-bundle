<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputFacebook;
use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FacebookPreBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
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
     */
    public function onPreBroadcast(PreBroadcastEvent $event)
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
    public static function getSubscribedEvents()
    {
        return array(PreBroadcastEvent::NAME => 'onPreBroadcast');
    }
}
