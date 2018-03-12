<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\StreamManager;
use PHPUnit\Framework\TestCase;

/**
 * Class BroadcastManagerTest
 */
class BroadcastManagerTest extends TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var StreamManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $streamManager;

    /**
     * Test getting a broadcast entity by id
     */
    public function testGetBroadcastByid(): void
    {
        $broadcastEntity = $this->createMock(LiveBroadcast::class);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(static::any())
            ->method('findOneBy')
            ->with([ 'broadcastId' => 10 ])
            ->willReturn($broadcastEntity);

        $this->entityManager->expects(static::any())
            ->method('getRepository')
            ->willReturn($broadcastRepository);

        $broadcast = new BroadcastManager($this->entityManager, $this->streamManager);
        $result = $broadcast->getBroadcastByid('10');

        self::assertInstanceOf(LiveBroadcast::class, $result);
    }

    /**
     * Test handling a broadcast's end
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function testHandleBroadcastEnd(): void
    {
        $this->streamManager->expects(static::any())
            ->method('endStream')
            ->willReturn(true);

        $youtubeChannel = $this->createMock(ChannelYouTube::class);

        $broadcastEntity = $this->createMock(LiveBroadcast::class);
        $broadcastEntity->expects(static::any())
            ->method('getOutputChannels')
            ->willReturn([$youtubeChannel]);

        $channel = $this->createMock(AbstractChannel::class);

        $broadcast = new BroadcastManager($this->entityManager, $this->streamManager);
        $broadcast->handleBroadcastEnd($broadcastEntity);
        $broadcast->handleBroadcastEnd($broadcastEntity, $channel);
        $this->addToAssertionCount(1);
    }

    /**
     * Setup mock objects
     */
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->streamManager = $this->createMock(StreamManager::class);
    }
}
