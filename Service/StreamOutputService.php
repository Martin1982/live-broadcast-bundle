<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;

/**
 * Class StreamOutputService
 * @package Martin1982\LiveBroadcastBundle\Service
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
    public function addStreamOutput(OutputInterface $streamOutput, $platform)
    {
        $this->streamOutputs[$platform] = $streamOutput;
    }

    /**
     * @param BaseChannel $channel
     *
     * @return OutputInterface
     *
     * @throws LiveBroadcastOutputException
     */
    public function getOutputInterface(BaseChannel $channel)
    {
        foreach ($this->streamOutputs as $streamOutput) {
            $channelType = $streamOutput->getChannelType();

            if ($channel instanceof $channelType) {
                $streamOutput->setChannel($channel);

                return $streamOutput;
            }
        }

        throw new LiveBroadcastOutputException('No OutputInterface configured for channel '.$channel->getChannelName());
    }
}
