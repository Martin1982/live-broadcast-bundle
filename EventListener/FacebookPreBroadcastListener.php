<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Event\PreBroadcastEvent;
use Martin1982\LiveBroadcastBundle\Events;
use Martin1982\LiveBroadcastBundle\Streams\Output\Facebook;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FacebookPreBroadcastListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class FacebookPreBroadcastListener implements EventSubscriberInterface
{
    /**
     * @param PreBroadcastEvent $event
     */
    public function onPreBroadcast(PreBroadcastEvent $event)
    {
        $liveBroadcast = $event->getLiveBroadcast();
        $output = $event->getOutput();

        if ($output instanceof Facebook) {
            // @todo: Facebook API call and set stream url
            $output->setStreamUrl('test');
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
