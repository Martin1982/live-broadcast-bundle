<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Event\StreamEndEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class StreamManager
 */
class StreamManager
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var AbstractSchedulerCommands
     */
    protected $commands;

    /**
     * StreamManager constructor.
     *
     * @param EventDispatcherInterface  $dispatcher
     * @param AbstractSchedulerCommands $commands
     */
    public function __construct(EventDispatcherInterface $dispatcher, AbstractSchedulerCommands $commands)
    {
        $this->dispatcher = $dispatcher;
        $this->commands = $commands;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function endStream(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $pid = $this->commands->getProcessIdForStream($broadcast->getBroadcastId(), $channel->getChannelId());
        if ($pid) {
            $this->commands->stopProcess($pid);
        }

        $event = new StreamEndEvent($broadcast, $channel);
        $this->dispatcher->dispatch(StreamEndEvent::NAME, $event);
    }
}
