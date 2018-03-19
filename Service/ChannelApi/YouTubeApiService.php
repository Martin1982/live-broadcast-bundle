<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEventRepository;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\YouTubeClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeApiService
 */
class YouTubeApiService implements ChannelApiInterface
{
    /**
     * @var string
     */
    public const STREAM_ACTIVE_STATUS = 'active';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $thumbnailDir;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Google_Client
     */
    protected $googleApiClient;

    /**
     * @var \Google_Service_YouTube
     */
    protected $youTubeApiClient;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var YouTubeClient
     */
    protected $client;

    /**
     * YouTubeApiService constructor
     *
     * @param EntityManager   $entityManager
     * @param LoggerInterface $logger
     * @param YouTubeClient   $youTubeClient
     */
    public function __construct(EntityManager $entityManager, LoggerInterface $logger, YouTubeClient $youTubeClient)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->client = $youTubeClient;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws LiveBroadcastOutputException
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->setChannel($channel);

        $youtubeBroadcast = $this->client->createBroadcast($broadcast);
        $stream           = $this->client->createStream($broadcast->getName());
        $youtubeBroadcast = $this->client->bind($youtubeBroadcast, $stream);

        $youTubeEvent = new YouTubeEvent();
        $youTubeEvent->setBroadcast($broadcast);
        $youTubeEvent->setChannel($channel);
        $youTubeEvent->setYouTubeId($youtubeBroadcast->getId());

        $this->entityManager->persist($youTubeEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws LiveBroadcastOutputException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $this->setChannel($channel);

        $eventRepository = $this->getEventRepository();
        $youTubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        if ($youTubeEvent) {
            $this->client->removeLivestream($youTubeEvent);
            $this->entityManager->remove($youTubeEvent);
            $this->entityManager->flush();
        }
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        $parts = 'id,brandingSettings';
        $opts = ['mine' => true];

        $client = $this->getYouTubeApiClient();

        if ($client) {
            $channels = $client->channels->listChannels($parts, $opts);

            if ($channels->count()) {
                /** @var \Google_Service_YouTube_Channel $channel */
                $channel = $channels->getItems()[0];

                /** @var \Google_Service_YouTube_ChannelBrandingSettings $branding */
                $branding = $channel->getBrandingSettings();

                return $branding->getChannel()->title;
            }
        }

        return null;
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @return string|null
     *
     * @throws LiveBroadcastOutputException
     */
    public function getStreamUrl(LiveBroadcast $broadcast, AbstractChannel $channel): ?string
    {
        $streamUrl = null;
        $this->setChannel($channel);

        $eventRepository = $this->entityManager->getRepository(YouTubeEvent::class);
        $event = $eventRepository->findBroadcastingToChannel($broadcast, $channel);
        if (!$event) {
            throw new LiveBroadcastOutputException('No event found');
        }
        $youTubeId = $event->getYouTubeId();

        $broadcast = $this->client->getYoutubeBroadcast($youTubeId);
        if ($broadcast) {
            $streamId  = $broadcast->getContentDetails()->getBoundStreamId();
            $streamUrl = $this->client->getStreamUrl($streamId);
        }

        return $streamUrl;
    }

    /**
     * @param \Google_Service_YouTube_LiveStream $stream
     *
     * @return string|null
     */
    public function getStreamUrlOld(\Google_Service_YouTube_LiveStream $stream): ?string
    {
        $streamAddress = $stream->getCdn()->getIngestionInfo()->getIngestionAddress();
        $streamName = $stream->getCdn()->getIngestionInfo()->getStreamName();

        return $streamAddress.'/'.$streamName;
    }

    /**
     * Retrieve a YouTube stream object
     *
     * @param LiveBroadcast  $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     *
     * @return \Google_Service_YouTube_LiveStream|null
     *
     * @throws LiveBroadcastOutputException
     */
    public function getStream(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube): ?\Google_Service_YouTube_LiveStream
    {
        $this->getAccessToken($channelYouTube->getRefreshToken());
        $eventRepository = $this->getEventRepository();
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        if (!$event) {
            return null;
        }

        $youTubeId = $event->getYouTubeId();
        $youTubeBroadcast = $this->getExternalBroadcastById($youTubeId);

        if (!$youTubeBroadcast) {
            return null;
        }

        $youTubeDetails = $youTubeBroadcast->getContentDetails();
        $streamId = $youTubeDetails->getBoundStreamId();

        return $this->getExternalStreamById($streamId);
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws LiveBroadcastOutputException
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $eventRepository = $this->getEventRepository();
        $youTubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        if (!$youTubeEvent) {
            $this->createLiveEvent($broadcast, $channel);

            return;
        }

        $this->updateLiveStream($youTubeEvent);
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     *
     * @return mixed
     *
     * @throws LiveBroadcastOutputException
     */
    public function getBroadcastStatus(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube)
    {
        $lifeCycleStatus = null;
        $this->getAccessToken($channelYouTube->getRefreshToken());

        $eventRepository = $this->getEventRepository();
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        if ($event) {
            $youTubeId = $event->getYouTubeId();
            $youTubeBroadcast = $this->getExternalBroadcastById($youTubeId);

            if ($youTubeBroadcast) {
                /** @var \Google_Service_YouTube_LiveBroadcastStatus $youTubeStatus */
                $youTubeStatus = $youTubeBroadcast->getStatus();
                $lifeCycleStatus = $youTubeStatus->getLifeCycleStatus();
            }
        }

        return $lifeCycleStatus;
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     * @param string         $broadcastState
     *
     * @return boolean
     *
     * @throws LiveBroadcastOutputException
     */
    public function transitionState(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube, $broadcastState): bool
    {
        $this->getAccessToken($channelYouTube->getRefreshToken());

        $eventRepository = $this->getEventRepository();
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        if (!$event) {
            return false;
        }

        $youTubeId = $event->getYouTubeId();
        $canChangeState = true;

        $stateRemoteTesting = YouTubeEvent::STATE_REMOTE_TESTING;
        $stateRemoteLive = YouTubeEvent::STATE_REMOTE_LIVE;

        if ($broadcastState === $stateRemoteTesting || $broadcastState === $stateRemoteLive) {
            $stream = $this->getStream($liveBroadcast, $channelYouTube);
            if (!$stream) {
                $canChangeState = false;
                $warning = sprintf('Can\'t change state when no stream is present for "%s"', $liveBroadcast);
                $this->logger->warning($warning);
            }

            $streamStatus = $stream->getStatus()->getStreamStatus();

            if (self::STREAM_ACTIVE_STATUS !== $streamStatus) {
                $canChangeState = false;
                $this->logger->warning(sprintf(
                    'Stream state must be \'active\' for "%s", current state is \'%s\'',
                    $liveBroadcast,
                    $streamStatus
                ));
            }
        }

        $client = $this->getYouTubeApiClient();
        if ($canChangeState && $client) {
            $this->logger->info('YouTube transition state', ['state' => $broadcastState]);
            try {
                $client->liveBroadcasts->transition($broadcastState, $youTubeId, 'status');

                return true;
            } catch (\Google_Service_Exception $exception) {
                $this->logger->error(
                    'YouTube transition state',
                    ['exception' => $exception->getMessage()]
                );
            }
        }

        return false;
    }

    /**
     * Edit a planned live event
     *
     * @param YouTubeEvent $event
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function updateLiveStream(YouTubeEvent $event): void
    {
        $channel = $event->getChannel();
        $this->getAccessToken($channel->getRefreshToken());
        $liveBroadcast = $event->getBroadcast();

        $broadcastResponse = $this->updateBroadcast($liveBroadcast, 'public', $event->getYouTubeId());
        $event->setYouTubeId($broadcastResponse->getId());

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string        $privacyStatus
     * @param string|null   $broadcastId
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws LiveBroadcastOutputException
     */
    protected function updateBroadcast(LiveBroadcast $liveBroadcast, $privacyStatus = 'public', $broadcastId = null): \Google_Service_YouTube_LiveBroadcast
    {
        $externalBroadcast = $this->setupBroadcast($liveBroadcast, $privacyStatus, $broadcastId);

        $client = $this->getYouTubeApiClient();
        if (!$client) {
            throw new LiveBroadcastOutputException('No API client available');
        }

        if (null !== $broadcastId) {
            return $client->liveBroadcasts->update('snippet,status', $externalBroadcast);
        }

        return $client->liveBroadcasts->insert('snippet,status', $externalBroadcast);
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string        $privacyStatus
     * @param string|null   $broadcastId
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws LiveBroadcastOutputException
     */
    protected function setupBroadcast(LiveBroadcast $liveBroadcast, $privacyStatus = 'public', $broadcastId = null): \Google_Service_YouTube_LiveBroadcast
    {
        $title = $liveBroadcast->getName();
        $description = $liveBroadcast->getDescription();
        $start = $liveBroadcast->getStartTimestamp();
        $end = $liveBroadcast->getEndTimestamp();
        $thumbnail = $liveBroadcast->getThumbnail();

        if (new \DateTime() > $start) {
            $start = new \DateTime();
            try {
                $start->add(new \DateInterval('PT1S'));
            } catch (\Exception $exception) {
                throw new LiveBroadcastOutputException(
                    $exception->getMessage(),
                    $exception->getCode(),
                    $exception->getPrevious()
                );
            }
        }

        $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($title);
        $broadcastSnippet->setDescription($description);
        $broadcastSnippet->setScheduledStartTime($start->format(\DateTime::ATOM));
        $broadcastSnippet->setScheduledEndTime($end->format(\DateTime::ATOM));

        if ($thumbnail instanceof File && $thumbnail->isFile()) {
            $defaultThumbnail = new \Google_Service_YouTube_Thumbnail();
            $thumbnailUrl = sprintf(
                '%s%s/%s',
                $this->host,
                $this->thumbnailDir,
                $thumbnail->getFilename()
            );
            $defaultThumbnail->setUrl($thumbnailUrl);

            [$width, $height] = getimagesize($thumbnail->getRealPath());
            $defaultThumbnail->setWidth($width);
            $defaultThumbnail->setHeight($height);

            $thumbnails = new \Google_Service_YouTube_ThumbnailDetails();
            $thumbnails->setDefault($defaultThumbnail);
            $broadcastSnippet->setThumbnails($thumbnails);
        }

        $status = new \Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus($privacyStatus);

        $broadcastInsert = new \Google_Service_YouTube_LiveBroadcast();
        if (null !== $broadcastId) {
            $broadcastInsert->setId($broadcastId);
        }
        $broadcastInsert->setSnippet($broadcastSnippet);
        $broadcastInsert->setStatus($status);
        $broadcastInsert->setKind('youtube#liveBroadcast');

        return $broadcastInsert;
    }

    /**
     * @param string $youTubeId
     *
     * @return \Google_Service_YouTube_LiveBroadcast|null
     *
     * @throws LiveBroadcastOutputException
     */
    protected function getExternalBroadcastById($youTubeId): ?\Google_Service_YouTube_LiveBroadcast
    {
        $client = $this->getYouTubeApiClient();
        if (!$client) {
            throw new LiveBroadcastOutputException('No API client available');
        }
        $broadcasts = $client->liveBroadcasts->listLiveBroadcasts('status,contentDetails', [
            'id' => $youTubeId,
        ])->getItems();

        if (!count($broadcasts)) {
            return null;
        }

        return $broadcasts[0];
    }

    /**
     * @param string $streamId
     *
     * @return \Google_Service_YouTube_LiveStream|null
     *
     * @throws LiveBroadcastOutputException
     */
    protected function getExternalStreamById($streamId): ?\Google_Service_YouTube_LiveStream
    {
        $client = $this->getYouTubeApiClient();
        if (!$client) {
            throw new LiveBroadcastOutputException('No API client available');
        }

        /** @var \Google_Service_YouTube_LiveStream[] $streamItems */
        $streamItems = $client->liveStreams->listLiveStreams('snippet,cdn,status', [
            'id' => $streamId,
        ])->getItems();

        if (!count($streamItems)) {
            return null;
        }

        return $streamItems[0];
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
     * @return YouTubeEventRepository
     */
    private function getEventRepository(): YouTubeEventRepository
    {
        return $this->entityManager->getRepository(YouTubeEvent::class);
    }
}
