<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeApiService
 */
class YouTubeApiService implements ChannelApiInterface
{
    /**
     * @var string|null
     */
    protected ?string $host = null;

    /**
     * YouTubeApiService constructor
     *
     * @param EntityManager   $entityManager
     * @param LoggerInterface $logger
     * @param YouTubeClient   $client
     */
    public function __construct(protected EntityManager $entityManager, protected LoggerInterface $logger, protected YouTubeClient $client)
    {
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastOutputException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws LiveBroadcastApiException
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->setChannel($channel);

        $youtubeBroadcast = $this->client->createBroadcast($broadcast);
        $this->client->addThumbnailToBroadcast($youtubeBroadcast, $broadcast);
        $stream = $this->client->createStream($broadcast->getName());
        $youtubeBroadcast = $this->client->bind($youtubeBroadcast, $stream);

        $streamEvent = new StreamEvent();
        $streamEvent->setBroadcast($broadcast);
        $streamEvent->setChannel($channel);
        $streamEvent->setExternalStreamId($youtubeBroadcast->getId());

        $this->entityManager->persist($streamEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastOutputException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->setChannel($channel);

        $eventRepository = $this->getEventRepository();
        $streamEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        if ($streamEvent) {
            $this->client->removeLiveStream($streamEvent);
            $this->entityManager->remove($streamEvent);
            $this->entityManager->flush();
        }
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastOutputException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->setChannel($channel);

        $eventRepository = $this->getEventRepository();
        $streamEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        if (!$streamEvent) {
            $this->createLiveEvent($broadcast, $channel);

            return;
        }

        $this->client->updateLiveStream($streamEvent);
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return string|null
     *
     * @throws LiveBroadcastOutputException
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException
     */
    public function getStreamUrl(LiveBroadcast $broadcast, AbstractChannel $channel): ?string
    {
        $streamUrl = null;
        $this->setChannel($channel);

        $eventRepository = $this->entityManager->getRepository(StreamEvent::class);
        $event = $eventRepository->findBroadcastingToChannel($broadcast, $channel);
        if (!$event) {
            throw new LiveBroadcastOutputException('No event found');
        }
        $streamId = (string) $event->getExternalStreamId();

        $youTubeBroadcast = $this->client->getYoutubeBroadcast($streamId);
        if ($youTubeBroadcast) {
            $streamId  = $youTubeBroadcast->getContentDetails()->getBoundStreamId();
            $streamUrl = $this->client->getStreamUrl($streamId);
        }

        return $streamUrl;
    }

    /**
     * @param PlannedChannelInterface $channel
     * @param int|string              $externalId
     *
     * @throws LiveBroadcastApiException
     * @throws LiveBroadcastOutputException
     */
    public function sendEndSignal(PlannedChannelInterface $channel, int|string $externalId): void
    {
        if (!$channel instanceof AbstractChannel) {
            return;
        }

        $this->setChannel($channel);
        $this->client->endLiveStream($externalId);
    }

    /**
     * Test if the API allows streaming
     *
     * @param AbstractChannel $channel
     *
     * @return bool
     *
     * @throws LiveBroadcastOutputException
     */
    public function canStream(AbstractChannel $channel): bool
    {
        if (!$channel instanceof ChannelYouTube) {
            throw new LiveBroadcastOutputException(sprintf('Expected youtube channel, got %s', \get_class($channel)));
        }

        $this->client->setChannel($channel);
        $this->client->getStreamsList();

        return true;
    }

    /**
     * @param AbstractChannel $channel
     *
     * @throws LiveBroadcastOutputException
     */
    private function setChannel(AbstractChannel $channel): void
    {
        if (!$channel instanceof ChannelYouTube) {
            throw new LiveBroadcastOutputException(sprintf('Expected youtube channel, got %s', \get_class($channel)));
        }

        $this->client->setChannel($channel);
    }

    /**
     * Get the YouTube Event repository
     *
     * @return StreamEventRepository
     */
    private function getEventRepository(): StreamEventRepository
    {
        return $this->entityManager->getRepository(StreamEvent::class);
    }
}
