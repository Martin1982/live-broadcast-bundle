<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\Message;

/**
 * Class StreamServiceAnnouncement
 */
class StreamServiceAnnouncement
{
    public const ACTION_PRE_PERSIST = 1;
    public const ACTION_PRE_UPDATE = 2;
    public const ACTION_PRE_REMOVE = 3;

    /**
     * @var int
     */
    private int $actionType = 0;

    /**
     * @var int
     */
    private int $broadcastId = 0;

    /**
     * @var int[]
     */
    private array $previousChannels = [];

    /**
     * StreamServiceAnnouncement constructor.
     *
     * @param int   $actionType
     * @param int   $broadcastId
     * @param array $previousChannels
     */
    public function __construct(int $actionType, int $broadcastId, array $previousChannels)
    {
        $this->setActionType($actionType);
        $this->setBroadcastId($broadcastId);
        $this->setPreviousChannels($previousChannels);
    }

    /**
     * @return int
     */
    public function getActionType(): int
    {
        return $this->actionType;
    }

    /**
     * @param int $actionType
     *
     * @return StreamServiceAnnouncement
     */
    public function setActionType(int $actionType): StreamServiceAnnouncement
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * @return int
     */
    public function getBroadcastId(): int
    {
        return $this->broadcastId;
    }

    /**
     * @param int $broadcastId
     *
     * @return StreamServiceAnnouncement
     */
    public function setBroadcastId(int $broadcastId): StreamServiceAnnouncement
    {
        $this->broadcastId = $broadcastId;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getPreviousChannels(): array
    {
        return $this->previousChannels;
    }

    /**
     * @param int[] $previousChannels
     *
     * @return StreamServiceAnnouncement
     */
    public function setPreviousChannels(array $previousChannels = []): StreamServiceAnnouncement
    {
        $this->previousChannels = $previousChannels;

        return $this;
    }
}
