<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputInterface;

/**
 * Class StreamOutputService
 */
class StreamOutputService
{
    /**
     * @var OutputInterface[]
     */
    private $streamOutputs = array();

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
     * @return OutputInterface
     * @throws LiveBroadcastException
     */
    public function getOutputInterface(BaseChannel $channel)
    {
        /** @var OutputInterface $streamOutput */
        foreach ($this->streamOutputs as $streamOutput) {
            if ($streamOutput->getChannelType() === get_class($channel)) {
                $streamOutput->setChannel($channel);

                return $streamOutput;
            }
        }

        throw new LiveBroadcastException('No OutputInterface configured for channel '.$channel->getChannelName());
    }
}
