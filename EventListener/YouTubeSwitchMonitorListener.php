<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYouTube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;

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
     * @var YouTubeApiService
     */
    protected $youTubeApiService;

    /**
     * YouTubeSwitchMonitorListener constructor.
     * @param SchedulerCommandsInterface $command
     * @param StreamOutputService $outputService
     * @param YouTubeApiService $youTubeApiService
     * @param Router $router
     * @param $redirectRoute
     */
    public function __construct(
        SchedulerCommandsInterface $command,
        StreamOutputService $outputService,
        YouTubeApiService $youTubeApiService,
        Router $router,
        $redirectRoute
    ) {
        $this->command = $command;
        $this->outputService = $outputService;
        $this->youTubeApiService = $youTubeApiService;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youTubeApiService->initApiClients($redirectUri);
    }

    /**
     * @param SwitchMonitorEvent $event
     */
    public function onSwitchMonitor(SwitchMonitorEvent $event)
    {
        $this->monitorBroadcast = $event->getMonitorBroadcast();
        $this->plannedBroadcast = $event->getPlannedBroadcast();
        $this->channel = $event->getChannel();

        $this->youTubeApiService->transitionState(
            $this->plannedBroadcast,
            $this->channel,
            YouTubeEvent::STATE_REMOTE_LIVE
        );

        $this->stopMonitorStream();
        $this->startBroadcast();
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(SwitchMonitorEvent::NAME => 'onSwitchMonitor');
    }

    /**
     * Stop a monitor stream
     */
    protected function stopMonitorStream()
    {
        $this->command->stopProcess($this->monitorBroadcast->getProcessId());
    }

    /**
     * Start the actual broadcast
     */
    protected function startBroadcast()
    {
        $input = $this->plannedBroadcast->getInput()->generateInputCmd();
        /** @var OutputYouTube $outputService */
        $outputService = $this->outputService->getOutputInterface($this->channel);
        $outputService->setStreamUrl($this->youTubeApiService->getStreamUrl($this->plannedBroadcast, $this->channel));

        $output = $outputService->generateOutputCmd();

        $this->command->startProcess($input, $output, array(
            'broadcast_id' => $this->plannedBroadcast->getBroadcastId(),
            'channel_id' => $this->channel->getChannelId(),
        ));
    }
}
