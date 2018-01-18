<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class YouTubeSwitchMonitorListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
 */
class YouTubeSwitchMonitorListener implements EventSubscriberInterface
{
    /**
     * @var RunningBroadcast
     */
    protected $monitorBroadcast;

    /**
     * @var LiveBroadcast
     */
    protected $plannedBroadcast;

    /**
     * @var ChannelYouTube
     */
    protected $channel;

    /**
     * @var SchedulerCommandsInterface
     */
    protected $command;

    /**
     * @var StreamOutputService
     */
    protected $outputService;

    /**
     * @var StreamInputService
     */
    protected $inputService;

    /**
     * @var YouTubeApiService
     */
    protected $youTubeApiService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * YouTubeSwitchMonitorListener constructor.
     * @param SchedulerCommandsInterface $command
     * @param StreamOutputService $outputService
     * @param StreamInputService $inputService
     * @param YouTubeApiService $youTubeApiService
     * @param GoogleRedirectService $redirectService
     * @param LoggerInterface $logger
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function __construct(
        SchedulerCommandsInterface $command,
        StreamOutputService $outputService,
        StreamInputService $inputService,
        YouTubeApiService $youTubeApiService,
        GoogleRedirectService $redirectService,
        LoggerInterface $logger
    ) {
        $this->command = $command;
        $this->outputService = $outputService;
        $this->inputService = $inputService;
        $this->youTubeApiService = $youTubeApiService;
        $this->logger = $logger;

        $redirectUri = $redirectService->getOAuthRedirectUrl();
        $this->youTubeApiService->initApiClients($redirectUri);
    }

    /**
     * @param SwitchMonitorEvent $event
     *
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function onSwitchMonitor(SwitchMonitorEvent $event)
    {
        $this->monitorBroadcast = $event->getMonitorBroadcast();
        $this->plannedBroadcast = $event->getPlannedBroadcast();
        $this->channel = $event->getChannel();

        if (!$this->channel instanceof ChannelYouTube) {
            return;
        }

        $transitionResult = $this->youTubeApiService->transitionState(
            $this->plannedBroadcast,
            $this->channel,
            YouTubeEvent::STATE_REMOTE_LIVE
        );

        if ($transitionResult === true) {
            $this->stopMonitorStream();
            $this->startBroadcast();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [SwitchMonitorEvent::NAME => 'onSwitchMonitor'];
    }

    /**
     * Stop a monitor stream
     */
    protected function stopMonitorStream()
    {
        $this->logger->info(
            'YouTube stop monitor stream',
            ['broadcast_id' => $this->monitorBroadcast->getBroadcastId()]
        );
        $this->command->stopProcess($this->monitorBroadcast->getProcessId());
    }

    /**
     * Start the actual broadcast
     *
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    protected function startBroadcast()
    {
        $media = $this->plannedBroadcast->getInput();
        $input = $this->inputService->getInputInterface($media)->generateInputCmd();

        /** @var OutputYouTube $outputService */
        $outputService = $this->outputService->getOutputInterface($this->channel);

        $stream = $this->youTubeApiService->getStream($this->plannedBroadcast, $this->channel);
        if (!$stream) {
            throw new LiveBroadcastInputException('No stream available');
        }
        $streamUrl = $this->youTubeApiService->getStreamUrl($stream);
        $outputService->setStreamUrl($streamUrl);

        $output = $outputService->generateOutputCmd();

        $this->logger->info(
            'YouTube start broadcast',
            ['broadcast_id' => $this->plannedBroadcast->getBroadcastId()]
        );
        $this->command->startProcess($input, $output, [
            'broadcast_id' => $this->plannedBroadcast->getBroadcastId(),
            'channel_id' => $this->channel->getChannelId(),
        ]);
    }
}
