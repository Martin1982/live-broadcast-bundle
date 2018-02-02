<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class StreamManagerTest
 */
class StreamManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ending of stream
     */
    public function testEndStream()
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(BaseChannel::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->any())
            ->method('dispatch')
            ->willReturn(true);

        $commands = $this->createMock(AbstractSchedulerCommands::class);
        $commands->expects($this->any())
            ->method('getProcessIdForStream')
            ->willReturn('123');
        $commands->expects($this->any())
            ->method('stopProcess')
            ->willReturn(true);

        $manager = new StreamManager($dispatcher, $commands);
        $manager->endStream($broadcast, $channel);
    }
}
