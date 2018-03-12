<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Broadcaster\AbstractSchedulerCommands;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class StreamManagerTest
 */
class StreamManagerTest extends TestCase
{
    /**
     * Test ending of stream
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testEndStream(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $channel = $this->createMock(AbstractChannel::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(static::any())
            ->method('dispatch')
            ->willReturn(true);

        $commands = $this->createMock(AbstractSchedulerCommands::class);
        $commands->expects(static::any())
            ->method('getProcessIdForStream')
            ->willReturn('123');
        $commands->expects(static::any())
            ->method('stopProcess')
            ->willReturn(true);

        $manager = new StreamManager($dispatcher, $commands);
        $manager->endStream($broadcast, $channel);
        $this->addToAssertionCount(1);
    }
}
