<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\MessageHandler;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Message\StreamServiceAnnouncement;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Class StreamServiceAnnouncementHandler
 */
#[AsMessageHandler]
class StreamServiceAnnouncementHandler
{
    /**
     * StreamServiceAnnouncementHandler constructor.
     *
     * @param BroadcastManager $manager
     * @param LoggerInterface  $logger
     */
    public function __construct(private BroadcastManager $manager, private LoggerInterface $logger)
    {
    }

    /**
     * Handle messages for stream announcements
     *
     * @param StreamServiceAnnouncement $message
     *
     * @return void
     */
    public function __invoke(StreamServiceAnnouncement $message): void
    {
        $actionType = $message->getActionType();
        $broadcast = $this->rebuildBroadcast($message->getBroadcastId(), $message->getPreviousChannels());
        try {
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
        } catch (LiveBroadcastApiException $exception) {
            $this->logger->error($exception->getMessage());
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
