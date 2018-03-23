<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
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
     * @var ChannelApiStack|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stack;

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

        $broadcast = new BroadcastManager($this->entityManager, $this->stack);
        $result = $broadcast->getBroadcastByid('10');

        self::assertInstanceOf(LiveBroadcast::class, $result);
    }

    /**
     * Setup mock objects
     */
    protected function setUp()
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->stack = $this->createMock(ChannelApiStack::class);
    }
}
