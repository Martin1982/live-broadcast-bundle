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
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeApiService
 */
class YouTubeApiService implements ChannelApiInterface
{
    public const STREAM_ACTIVE_STATUS = 'active';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

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
     * YouTubeApiService constructor
     *
     * @param string                $clientId
     * @param string                $clientSecret
     * @param string                $host
     * @param string                $thumbnailDir
     * @param EntityManager         $entityManager
     * @param LoggerInterface       $logger
     * @param GoogleRedirectService $redirectService
     *
     * @throws LiveBroadcastOutputException
     */
    public function __construct($clientId, $clientSecret, $host, $thumbnailDir, EntityManager $entityManager, LoggerInterface $logger, GoogleRedirectService $redirectService)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->host = $host;
        $this->thumbnailDir = $thumbnailDir;
        $this->entityManager = $entityManager;
        $this->logger = $logger;

        $this->initApiClients($redirectService->getOAuthRedirectUrl());
    }

    /**
     * Initialize API to Google and YouTube
     *
     * @param string $oAuthRedirectUrl
     *
     * @throws LiveBroadcastOutputException
     */
    public function initApiClients($oAuthRedirectUrl): void
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new LiveBroadcastOutputException('The YouTube oAuth settings are not correct.');
        }

        $this->setGoogleApiClient(new \Google_Client());
        $this->googleApiClient->setLogger($this->logger);
        $this->googleApiClient->setClientId($this->clientId);
        $this->googleApiClient->setClientSecret($this->clientSecret);
        $this->googleApiClient->setScopes(['https://www.googleapis.com/auth/youtube']);
        $this->googleApiClient->setAccessType('offline');
        $this->googleApiClient->setRedirectUri($oAuthRedirectUrl);
        $this->googleApiClient->setApprovalPrompt('force');

        $this->setYouTubeApiClient(new \Google_Service_YouTube($this->googleApiClient));
    }

    /**
     * @param \Google_Client $client
     */
    public function setGoogleApiClient(\Google_Client $client): void
    {
        $this->googleApiClient = $client;
    }

    /**
     * @param \Google_Service_YouTube $youtubeClient
     */
    public function setYouTubeApiClient(\Google_Service_YouTube $youtubeClient): void
    {
        $this->youTubeApiClient = $youtubeClient;
    }

    /**
     * @param string $refreshToken
     *
     * @return array|null
     */
    public function getAccessToken($refreshToken): ?array
    {
        $this->googleApiClient->fetchAccessTokenWithRefreshToken($refreshToken);

        return $this->googleApiClient->getAccessToken();
    }

    /**
     * Set the access token
     *
     * @param string $sessionToken
     *
     * @throws \InvalidArgumentException
     */
    public function setAccessToken($sessionToken): void
    {
        $this->googleApiClient->setAccessToken($sessionToken);
    }

    /**
     * Check if the client has authenticated the user
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return (bool) $this->googleApiClient->getAccessToken();
    }

    /**
     * @param string $requestCode
     * @param string $requestState
     * @param string $sessionState
     *
     * @return array|null
     */
    public function authenticate($requestCode, $requestState, $sessionState): ?array
    {
        $sessionState = (string) $sessionState;
        $requestState = (string) $requestState;
        if ($sessionState !== $requestState) {
            return null;
        }

        $this->googleApiClient->authenticate($requestCode);

        return $this->googleApiClient->getAccessToken();
    }

    /**
     * Clear auth token
     */
    public function clearToken(): void
    {
        $this->googleApiClient->revokeToken();
    }

    /**
     * Retrieve the client's refresh token
     *
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->googleApiClient->getRefreshToken();
    }

    /**
     * Get the authentication URL for the googleclient
     *
     * @param string $state
     *
     * @return string
     */
    public function getAuthenticationUrl($state): string
    {
        $this->googleApiClient->setState($state);

        return $this->googleApiClient->createAuthUrl();
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        $parts = 'id,brandingSettings';
        $opts = ['mine' => true];

        $channels = $this->youTubeApiClient->channels->listChannels($parts, $opts);

        if ($channels->count()) {
            /** @var \Google_Service_YouTube_Channel $channel */
            $channel = $channels->getItems()[0];

            /** @var \Google_Service_YouTube_ChannelBrandingSettings $branding */
            $branding = $channel->getBrandingSettings();

            return $branding->getChannel()->title;
        }

        return null;
    }

    /**
     * @param \Google_Service_YouTube_LiveStream $stream
     *
     * @return string|null
     */
    public function getStreamUrl(\Google_Service_YouTube_LiveStream $stream): ?string
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function createLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $youTubeData = $this->setupLivestream($broadcast, $channel);

        $youTubeEvent = new YouTubeEvent();
        $youTubeEvent->setBroadcast($broadcast);
        $youTubeEvent->setChannel($channel);
        $youTubeEvent->setYouTubeId($youTubeData->getId());

        $this->entityManager->persist($youTubeEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
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
     * @param LiveBroadcast   $broadcast
     * @param AbstractChannel $channel
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, AbstractChannel $channel): void
    {
        $eventRepository = $this->getEventRepository();
        $youTubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        if ($youTubeEvent) {
            $this->removeLivestream($youTubeEvent);
            $this->entityManager->remove($youTubeEvent);
            $this->entityManager->flush();
        }
    }

    /**
     * @param LiveBroadcast  $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     *
     * @return mixed
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

        if ($canChangeState) {
            $this->logger->info('YouTube transition state', ['state' => $broadcastState]);
            try {
                $this->youTubeApiClient->liveBroadcasts->transition($broadcastState, $youTubeId, 'status');

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
     * @param LiveBroadcast  $liveBroadcast
     * @param ChannelYouTube $channel
     * @param string         $status
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws \Exception
     */
    protected function setupLivestream(LiveBroadcast $liveBroadcast, ChannelYouTube $channel, $status = 'public'): \Google_Service_YouTube_LiveBroadcast
    {
        $this->getAccessToken($channel->getRefreshToken());

        $broadcastResponse = $this->updateBroadcast($liveBroadcast, $status);
        $streamsResponse = $this->createStream($liveBroadcast->getName());

        // Bind Broadcast and Stream
        $bindedResponse = $this->youTubeApiClient->liveBroadcasts->bind(
            $broadcastResponse->getId(),
            'id,contentDetails',
            ['streamId' => $streamsResponse->getId()]
        );

        return $bindedResponse;
    }

    /**
     * Edit a planned live event
     *
     * @param YouTubeEvent $event
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
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
     * Remove a planned live event on YouTube
     *
     * @param YouTubeEvent $event
     */
    protected function removeLivestream(YouTubeEvent $event): void
    {
        $channel = $event->getChannel();
        $this->getAccessToken($channel->getRefreshToken());

        try {
            $this->youTubeApiClient->liveBroadcasts->delete($event->getYouTubeId());
        } catch (\Google_Service_Exception $exception) {
            $this->logger->error(
                'YouTube remove live stream',
                [
                    'broadcast_id' => $event->getBroadcast()->getBroadcastId(),
                    'broadcast_name' => $event->getBroadcast()->getName(),
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string        $privacyStatus
     * @param string|null   $broadcastId
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws \Exception
     */
    protected function updateBroadcast(LiveBroadcast $liveBroadcast, $privacyStatus = 'public', $broadcastId = null): \Google_Service_YouTube_LiveBroadcast
    {
        $externalBroadcast = $this->setupBroadcast($liveBroadcast, $privacyStatus, $broadcastId);

        if (null !== $broadcastId) {
            return $this->youTubeApiClient->liveBroadcasts->update('snippet,status', $externalBroadcast);
        }

        return $this->youTubeApiClient->liveBroadcasts->insert('snippet,status', $externalBroadcast);
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string        $privacyStatus
     * @param string|null   $broadcastId
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws \Exception
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
            $start->add(new \DateInterval('PT1S'));
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
     * @param string $title
     *
     * @return \Google_Service_YouTube_LiveStream
     */
    protected function createStream($title): \Google_Service_YouTube_LiveStream
    {
        $streamSnippet = new \Google_Service_YouTube_LiveStreamSnippet();
        $streamSnippet->setTitle($title);

        $cdn = new \Google_Service_YouTube_CdnSettings();
        $cdn->setFormat('720p');
        $cdn->setIngestionType('rtmp');

        $streamInsert = new \Google_Service_YouTube_LiveStream();
        $streamInsert->setSnippet($streamSnippet);
        $streamInsert->setCdn($cdn);
        $streamInsert->setKind('youtube#liveStream');

        return $this->youTubeApiClient->liveStreams->insert('snippet,cdn', $streamInsert);
    }

    /**
     * @param string $youTubeId
     *
     * @return \Google_Service_YouTube_LiveBroadcast|null
     */
    protected function getExternalBroadcastById($youTubeId): ?\Google_Service_YouTube_LiveBroadcast
    {
        $broadcasts = $this->youTubeApiClient->liveBroadcasts->listLiveBroadcasts('status,contentDetails', [
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
     */
    protected function getExternalStreamById($streamId): ?\Google_Service_YouTube_LiveStream
    {
        /** @var \Google_Service_YouTube_LiveStream[] $streamItems */
        $streamItems = $this->youTubeApiClient->liveStreams->listLiveStreams('snippet,cdn,status', [
            'id' => $streamId,
        ])->getItems();

        if (!count($streamItems)) {
            return null;
        }

        return $streamItems[0];
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
