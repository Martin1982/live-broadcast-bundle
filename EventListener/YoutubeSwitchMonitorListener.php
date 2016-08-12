<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Broadcaster\RunningBroadcast;
use Martin1982\LiveBroadcastBundle\Broadcaster\SchedulerCommandsInterface;
use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEvent;
use Martin1982\LiveBroadcastBundle\Event\SwitchMonitorEvent;
use Martin1982\LiveBroadcastBundle\Service\StreamOutput\OutputYoutube;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;

class YoutubeSwitchMonitorListener implements EventSubscriberInterface
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
     * @var ChannelYoutube
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
     * @var YouTubeLiveService
     */
    protected $youtubeLiveService;

    /**
     * YoutubeSwitchMonitorListener constructor.
     * @param SchedulerCommandsInterface $command
     * @param StreamOutputService $outputService
     * @param YouTubeLiveService $youtubeLiveService
     * @param Router $router
     * @param $redirectRoute
     */
    public function __construct(
        SchedulerCommandsInterface $command,
        StreamOutputService $outputService,
        YouTubeLiveService $youtubeLiveService,
        Router $router,
        $redirectRoute
    ){
        $this->command = $command;
        $this->outputService = $outputService;
        $this->youtubeLiveService = $youtubeLiveService;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youtubeLiveService->initApiClients($redirectUri);
    }

    /**
     * @param SwitchMonitorEvent $event
     */
    public function onSwitchMonitor(SwitchMonitorEvent $event)
    {
        $this->monitorBroadcast = $event->getMonitorBroadcast();
        $this->plannedBroadcast = $event->getPlannedBroadcast();
        $this->channel = $event->getChannel();

        $this->youtubeLiveService->transitionState(
            $this->plannedBroadcast,
            $this->channel,
            YoutubeEvent::STATE_REMOTE_LIVE
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
        /** @var OutputYoutube $outputService */
        $outputService = $this->outputService->getOutputInterface($this->channel);
        $outputService->setStreamUrl($this->youtubeLiveService->getStreamUrl($this->plannedBroadcast, $this->channel));

        $output = $outputService->generateOutputCmd();

        $this->command->startProcess($input, $output, array(
            'broadcast_id' => $this->plannedBroadcast->getBroadcastId(),
            'channel_id' => $this->channel->getChannelId(),
        ));
    }
}