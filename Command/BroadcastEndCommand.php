<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Command;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BroadcastEndCommand
 *
 * @codeCoverageIgnore
 */
class BroadcastEndCommand extends Command
{
    /**
     * @var BroadcastManager
     */
    protected $broadcastManager;

    /**
     * @var string
     */
    protected static $defaultName = 'livebroadcaster:broadcast:end';

    /**
     * BroadcastEndCommand constructor
     *
     * @param BroadcastManager $broadcastManager
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(BroadcastManager $broadcastManager)
    {
        $this->broadcastManager = $broadcastManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Stop a broadcast and handle completion on it\'s channels');
        $this->addArgument('broadcast', InputArgument::REQUIRED, 'Broadcast id');
        $this->addArgument('channel', InputArgument::REQUIRED, 'Channel id');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $broadcastId = $input->getArgument('broadcast');
        $channelId = $input->getArgument('channel');

        $broadcast = $this->broadcastManager->getBroadcastByid($broadcastId);

        if ($broadcast instanceof LiveBroadcast) {
            $channel = $this->getChannel($broadcast, $channelId);
            $this->broadcastManager->handleBroadcastEnd($broadcast, $channel);
        }
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param null|string   $channelId
     *
     * @return AbstractChannel|null
     */
    protected function getChannel(LiveBroadcast $broadcast, $channelId = null): ?AbstractChannel
    {
        $channels = null;
        $selectedChannel = null;

        if ($channelId) {
            $channels = $broadcast->getOutputChannels();
        }

        if ($channels) {
            foreach ($channels as $channel) {
                if ((string) $channel->getChannelId() === (string) $channelId) {
                    $selectedChannel = $channel;
                }
            }
        }

        return $selectedChannel;
    }
}
