<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\MessageHandler;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Message\StreamServiceAnnouncement;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class StreamServiceAnnouncementHandler
 */
class StreamServiceAnnouncementHandler implements MessageHandlerInterface
{
    /**
     * @var BroadcastManager
     */
    private $manager;

    /**
     * StreamServiceAnnouncementHandler constructor.
     *
     * @param BroadcastManager $manager
     */
    public function __construct(BroadcastManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle messages for stream announcements
     *
     * @param StreamServiceAnnouncement $message
     *
     * @return void
     */
    public function __invoke(StreamServiceAnnouncement $message)
    {
        $actionType = $message->getActionType();
        $broadcast = $this->rebuildBroadcast($message->getBroadcastId(), $message->getPreviousChannels());
        switch ($actionType) {
            case StreamServiceAnnouncement::ACTION_PRE_PERSIST:
                $this->manager->preInsert($broadcast);
                break;
            case StreamServiceAnnouncement::ACTION_PRE_UPDATE:
                $this->manager->preUpdate($broadcast);
                break;
            case StreamServiceAnnouncement::ACTION_PRE_REMOVE:
                $this->manager->preDelete($broadcast);
                break;
            default:
                break;
        }
    }

    /**
     * FunctionDescription
     *
     * @param int   $id
     * @param int[] $channels
     *
     * @return LiveBroadcast
     */
    protected function rebuildBroadcast(int $id, array $channels): LiveBroadcast
    {
        $broadcast = $this->manager->getBroadcastById($id);
        if (!$broadcast) {
            $broadcast = new LiveBroadcast();
        }

        $outputChannels = [];
        foreach ($channels as $channelId) {
            $outputChannels[] = $this->manager->getChannelById($channelId);
        }

        $broadcast->setOutputChannels($outputChannels);

        return $broadcast;
    }
}
