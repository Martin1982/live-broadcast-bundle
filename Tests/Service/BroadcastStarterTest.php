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
use Martin1982\LiveBroadcastBundle\Entity\Media\AbstractMedia;
use Martin1982\LiveBroadcastBundle\Service\BroadcastStarter;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\DynamicStreamUrlInterface;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class BroadcastStarterTest
 */
class BroadcastStarterTest extends TestCase
{
    /**
     * @var StreamInputService|MockObject
     */
    protected $input;

    /**
     * @var StreamOutputService|MockObject
     */
    protected $output;

    /**
     * @var AbstractSchedulerCommands|MockObject
     */
    protected $commands;

    /**
     * Test that a broadcast can get started
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function testStartBroadcast(): void
    {
        $media = $this->createMock(AbstractMedia::class);

        $inputInterface = $this->createMock(InputInterface::class);
        $inputInterface->expects(self::atLeastOnce())
            ->method('generateInputCmd')
            ->willReturn('inputcmd');

        $outputInterface = $this->createMock(DynamicStreamUrlInterface::class);
        $outputInterface->expects(self::atLeastOnce())
            ->method('setBroadcast');
        $outputInterface->expects(self::atLeastOnce())
            ->method('generateOutputCmd')
            ->willReturn('output');

        $broadcast = $this->createMock(LiveBroadcast::class);
        $broadcast->expects(self::atLeastOnce())
            ->method('getInput')
            ->willReturn($media);
        $broadcast->expects(self::atLeastOnce())
            ->method('isStopOnEndTimestamp')
            ->willReturn(true);
        $broadcast->expects(self::atLeastOnce())
            ->method('getBroadcastId')
            ->willReturn(11);

        $channel = $this->createMock(AbstractChannel::class);
        $channel->expects(self::atLeastOnce())
            ->method('getChannelId')
            ->willReturn(21);

        $this->input->expects(self::atLeastOnce())
            ->method('getInputInterface')
            ->willReturn($inputInterface);

        $this->output->expects(self::atLeastOnce())
            ->method('getOutputInterface')
            ->willReturn($outputInterface);

        $this->commands->expects(self::atLeastOnce())
            ->method('setLooping');
        $this->commands->expects(self::atLeastOnce())
            ->method('startProcess')
            ->willReturn('');

        $starter = new BroadcastStarter($this->input, $this->output, $this->commands);
        $starter->startBroadcast($broadcast, $channel);
    }

    /**
     * Setup basic mocks
     */
    protected function setUp(): void
    {
        $this->input = $this->createMock(StreamInputService::class);
        $this->output = $this->createMock(StreamOutputService::class);
        $this->commands = $this->createMock(AbstractSchedulerCommands::class);
    }
}
