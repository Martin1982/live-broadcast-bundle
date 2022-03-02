<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Broadcaster;

use Martin1982\LiveBroadcastBundle\Broadcaster\Scheduler;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcastRepository;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Martin1982\LiveBroadcastBundle\Service\BroadcastStarter;
use Martin1982\LiveBroadcastBundle\Service\ChannelValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class SchedulerTest
 */
class SchedulerTest extends TestCase
{
    /**
     * @var ChannelValidatorService|MockObject
     */
    protected $validator;

    /**
     * @var BroadcastStarter|MockObject
     */
    protected $broadcastStarter;

    /**
     * @var BroadcastManager|MockObject
     */
    protected $broadcastManager;

    /**
     * @var SchedulerCommandsInterface|MockObject
     */
    protected $schedulerCommands;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * Test stopping an expired broadcast
     *
     * @throws LiveBroadcastException
     */
    public function testStopAnExpiredBroadcast(): void
    {
        $expiredBroadcast = new LiveBroadcast();
        $expiredBroadcast->setEndTimestamp(new \DateTime('-1 hour'));
        $expiredBroadcast->setStopOnEndTimestamp(true);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('find')
            ->willReturn($expiredBroadcast);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('stopProcess')
            ->willReturn('');

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * @throws LiveBroadcastException
     */
    public function testStoppingCausesAnError(): void
    {
        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('find')
            ->willReturn(null);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $this->logger->expects(self::atLeastOnce())
            ->method('error');

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test starting a planned broadcast
     *
     * @throws LiveBroadcastException
     */
    public function testStartPlannedBroadcast(): void
    {
        $channel = $this->createMock(AbstractChannel::class);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn([$channel]);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+5 minutes'));

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('find')
            ->willReturn($plannedBroadcast);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([$plannedBroadcast]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $this->broadcastStarter->expects(self::atLeastOnce())
            ->method('startBroadcast');

        $this->logger->expects(self::atLeastOnce())
            ->method('info');

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test starting a planned broadcast which has already started
     *
     * @throws LiveBroadcastException
     */
    public function testStartPlannedBroadcastAlreadyStarted(): void
    {
        $channel = $this->createMock(AbstractChannel::class);
        $channel->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn([$channel]);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+5 minutes'));

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('find')
            ->willReturn($plannedBroadcast);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([$plannedBroadcast]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $this->broadcastStarter->expects(self::never())
            ->method('startBroadcast');

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test starting a planned broadcast which throws an exception logs an error
     *
     * @throws LiveBroadcastException
     */
    public function testStartPlannedBroadcastThrowsException(): void
    {
        $channel = $this->createMock(AbstractChannel::class);
        $channel->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(30);

        $plannedBroadcast = $this->createMock(LiveBroadcast::class);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getOutputChannels')
            ->willReturn([$channel]);
        $plannedBroadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('+5 minutes'));

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);
        $broadcastRepository->expects(self::atLeastOnce())
            ->method('find')
            ->willReturn($plannedBroadcast);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([$plannedBroadcast]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $this->broadcastStarter->expects(self::atLeastOnce())
            ->method('startBroadcast')
            ->willThrowException(new \Exception('Something went wrong'));

        $this->logger->expects(self::atLeastOnce())
            ->method('error');

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test that end signals get sent
     *
     * @throws LiveBroadcastException
     */
    public function testSendEndSignal(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('isStopOnEndTimestamp')
            ->willReturn(true);
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('-5 minutes'));

        $channel = $this->createMock(ChannelFacebook::class);

        $eventMock = $this->createMock(StreamEvent::class);
        $eventMock->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $eventMock->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([$eventMock]);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test that end signals get sent
     *
     * @throws LiveBroadcastException
     */
    public function testSendEndSignalWhenNoChannelIsFound(): void
    {
        $broadcast = null;
        $channel = null;

        $eventMock = $this->createMock(StreamEvent::class);
        $eventMock->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $eventMock->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([$eventMock]);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test that end signals get sent
     *
     * @throws LiveBroadcastException
     */
    public function testSendEndSignalWhenNotStarted(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('+10 minutes'));

        $channel = $this->createMock(ChannelFacebook::class);

        $eventMock = $this->createMock(StreamEvent::class);
        $eventMock->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $eventMock->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([$eventMock]);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn([]);

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Test that end signals get sent
     *
     * @throws LiveBroadcastException
     */
    public function testSendEndSignalWithSingleVideoRun(): void
    {
        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getStartTimestamp')
            ->willReturn(new \DateTime('-10 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('isStopOnEndTimestamp')
            ->willReturn(false);
        $broadcast->expects(self::atLeastOnce())
            ->method('getEndTimestamp')
            ->willReturn(new \DateTime('-5 minutes'));
        $broadcast->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(20);

        $channel = $this->createMock(ChannelFacebook::class);
        $channel->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(30);

        $eventMock = $this->createMock(StreamEvent::class);
        $eventMock->expects(self::atLeastOnce())
            ->method('getBroadcast')
            ->willReturn($broadcast);
        $eventMock->expects(self::atLeastOnce())
            ->method('getChannel')
            ->willReturn($channel);

        $broadcastRepository = $this->createMock(LiveBroadcastRepository::class);

        $eventsRepository = $this->createMock(StreamEventRepository::class);
        $eventsRepository->expects(self::atLeastOnce())
            ->method('findBy')
            ->willReturn([$eventMock]);

        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getRunningProcesses')
            ->willReturn(['a process']);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(10);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getProcessId')
            ->willReturn(1010);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(20);
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('phpunit');
        $this->schedulerCommands->expects(self::atLeastOnce())
            ->method('getKernelEnvironment')
            ->willReturn('phpunit');

        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getPlannedBroadcasts')
            ->willReturn([]);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getBroadcastsRepository')
            ->willReturn($broadcastRepository);
        $this->broadcastManager->expects(self::atLeastOnce())
            ->method('getEventsRepository')
            ->willReturn($eventsRepository);

        $scheduler = $this->getScheduler();
        $scheduler->applySchedule();
    }

    /**
     * Get a scheduler instance
     *
     * @return Scheduler
     */
    protected function getScheduler(): Scheduler
    {
        return new Scheduler(
            $this->validator,
            $this->broadcastStarter,
            $this->broadcastManager,
            $this->schedulerCommands,
            $this->logger
        );
    }

    /**
     * Setup default mocks
     */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ChannelValidatorService::class);
        $this->broadcastStarter = $this->createMock(BroadcastStarter::class);
        $this->broadcastManager = $this->createMock(BroadcastManager::class);
        $this->schedulerCommands = $this->createMock(SchedulerCommandsInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }
}
