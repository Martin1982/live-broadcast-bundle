<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Doctrine\ORM\EntityRepository;
use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class SchedulerTest
 */
class SchedulerTest extends TestCase
{
    /**
     * @var BroadcastManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $broadcastManager;

    /**
     * @var SchedulerCommandsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $schedulerCommands;

    /**
     * @var StreamOutputService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $outputService;

    /**
     * @var StreamInputService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inputService;

    /**
     * @var EventDispatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * Test applying a schedule
     *
     * @throws LiveBroadcastException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testApplySchedule(): void
    {
        $eventRepository = $this->createMock(EntityRepository::class);
        $eventRepository->expects(static::any())
            ->method('findBy')
            ->willReturn([]);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(static::any())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);

        $this->broadcastManager->expects(self::any())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::any())
            ->method('getEventsRepository')
            ->willReturn($eventRepository);

        $this->dispatcher->expects(static::any())
            ->method('dispatch')
            ->willReturn(true);

        $this->logger->expects(static::any())
            ->method('error')
            ->willReturn(true);
        $this->logger->expects(static::any())
            ->method('info')
            ->willReturn(true);
        $this->logger->expects(static::any())
            ->method('debug')
            ->willReturn(true);

        $this->schedulerCommands->expects(static::any())
            ->method('getRunningProcesses')
            ->willReturn([]);

        $scheduler = new Scheduler(
            $this->broadcastManager,
            $this->schedulerCommands,
            $this->outputService,
            $this->inputService,
            $this->logger
        );

        $scheduler->applySchedule();
        $this->addToAssertionCount(1);
    }

    /**
     * Setup default mocks
     */
    protected function setUp()
    {
        $this->broadcastManager = $this->createMock(BroadcastManager::class);
        $this->schedulerCommands = $this->createMock(SchedulerCommandsInterface::class);
        $this->outputService = $this->createMock(StreamOutputService::class);
        $this->inputService = $this->createMock(StreamInputService::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
