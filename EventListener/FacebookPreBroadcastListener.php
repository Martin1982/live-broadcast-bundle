<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Events;
use Martin1982\LiveBroadcastBundle\Streams\Output\Facebook;
use Martin1982\LiveBroadcastBundle\Streams\Service\FacebookLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FacebookPreBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class FacebookPreBroadcastListener implements EventSubscriberInterface
{
    /**
     * @var FacebookLiveService
     */
    private $facebookLiveService;

    /**
     * FacebookPreBroadcastListener constructor.
     * @param FacebookLiveService $facebookLiveService
     */
    public function __construct(FacebookLiveService $facebookLiveService)
    {
        $this->facebookLiveService = $facebookLiveService;
    }

    /**
     * @param PreBroadcastEvent $event
     */
    public function onPreBroadcast(PreBroadcastEvent $event)
    {
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof Facebook) {
            $streamUrl = $this->facebookLiveService->createFacebookLiveVideo($liveBroadcast, $output);
            $output->setStreamUrl($streamUrl);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(Events::LIVE_BROADCAST_PRE_BROADCAST => 'onPreBroadcast');
    }
}
