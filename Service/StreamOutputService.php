<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;

/**
 * Class StreamOutputService
 */
class StreamOutputService
{
    /**
     * @var OutputInterface[]
     */
    private $streamOutputs = [];

    /**
     * @param OutputInterface $streamOutput
     * @param string          $platform
     */
    public function addStreamOutput(OutputInterface $streamOutput, $platform): void
    {
        $this->streamOutputs[$platform] = $streamOutput;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @return OutputInterface
     *
     * @throws LiveBroadcastOutputException
     */
    public function getOutputInterface(AbstractChannel $channel): OutputInterface
    {
        foreach ($this->streamOutputs as $streamOutput) {
            $channelType = $streamOutput->getChannelType();

            if ($channel instanceof $channelType) {
                $streamOutput->setChannel($channel);

                return $streamOutput;
            }
        }

        $error = sprintf('No OutputInterface configured for channel %s', $channel->getChannelName());
        throw new LiveBroadcastOutputException($error);
    }
}
