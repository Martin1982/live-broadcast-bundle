<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\DynamicStreamUrlInterface;

/**
 * Class BroadcastStarter
 */
class BroadcastStarter
{
    /**
     * @var StreamInputService
     */
    protected $inputService;

    /**
     * @var StreamOutputService
     */
    protected $outputService;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $commands;

    /**
     * BroadcastStarter constructor
     *
     * @param StreamInputService         $input
     * @param StreamOutputService        $output
     * @param SchedulerCommandsInterface $commands
     */
    public function __construct(StreamInputService $input, StreamOutputService $output, SchedulerCommandsInterface $commands)
    {
        $this->inputService = $input;
        $this->outputService = $output;
        $this->commands = $commands;
    }

    /**
     * Initiate a new broadcast
     *
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function startBroadcast(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $input = $this->inputService->getInputInterface($broadcast->getInput());
        $output = $this->outputService->getOutputInterface($channel);

        if ($output instanceof DynamicStreamUrlInterface) {
            $output->setBroadcast($broadcast);
        }

        $this->commands->setLoopable($broadcast->isStopOnEndTimestamp());
        $this->commands->startProcess($input->generateInputCmd(), $output->generateOutputCmd(), [
            'broadcast_id' => $broadcast->getBroadcastId(),
            'channel_id' => $channel->getChannelId(),
        ]);
    }
}
