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
     * BroadcastStarter constructor
     *
     * @param StreamInputService         $inputService
     * @param StreamOutputService        $outputService
     * @param SchedulerCommandsInterface $commands
     */
    public function __construct(protected StreamInputService $inputService, protected StreamOutputService $outputService, protected SchedulerCommandsInterface $commands)
    {
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

        $this->commands->setLooping($broadcast->isStopOnEndTimestamp());
        $this->commands->startProcess($input->generateInputCmd(), $output->generateOutputCmd(), [
            'broadcast_id' => $broadcast->getBroadcastId(),
            'channel_id' => $channel->getChannelId(),
        ]);
    }
}
