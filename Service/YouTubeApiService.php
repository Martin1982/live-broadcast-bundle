<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YouTubeEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeApiService
 * @package Martin1982\LiveBroadcastBundle\Service
 */
class YouTubeApiService
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

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
     * YouTubeApiService constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @throws LiveBroadcastOutputException
     */
    public function __construct(
        $clientId,
        $clientSecret,
        EntityManager $entityManager,
        LoggerInterface $logger
    ) {
        if (empty($clientId) || empty($clientSecret)) {
            throw new LiveBroadcastOutputException('The YouTube oAuth settings are not correct.');
        }

        $this->entityManager = $entityManager;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
    }

    /**
     * Initialize API to Google and YouTube
     *
     * @param $oAuthRedirectUrl
     */
    public function initApiClients($oAuthRedirectUrl)
    {
        $googleApiClient = new \Google_Client();
        $googleApiClient->setLogger($this->logger);
        $googleApiClient->setClientId($this->clientId);
        $googleApiClient->setClientSecret($this->clientSecret);
        $googleApiClient->setScopes('https://www.googleapis.com/auth/youtube');
        $googleApiClient->setAccessType('offline');
        $googleApiClient->setRedirectUri($oAuthRedirectUrl);

        $this->googleApiClient = $googleApiClient;
        $this->youTubeApiClient = new \Google_Service_YouTube($googleApiClient);
    }

    /**
     * @param $refreshToken
     * @return array
     */
    public function getAccessToken($refreshToken)
    {
        $this->googleApiClient->fetchAccessTokenWithRefreshToken($refreshToken);

        return $this->googleApiClient->getAccessToken();
    }

    /**
     * Set the access token
     * @param string $sessionToken
     */
    public function setAccessToken($sessionToken)
    {
        $this->googleApiClient->setAccessToken($sessionToken);
    }

    /**
     * Check if the client has authenticated the user
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return (bool) $this->googleApiClient->getAccessToken();
    }

    /**
     * @param $requestCode
     * @param $requestState
     * @param $sessionState
     * @return array|void
     */
    public function authenticate($requestCode, $requestState, $sessionState)
    {
        if ((string) $sessionState !== (string) $requestState) {
            return;
        }

        $this->googleApiClient->authenticate($requestCode);

        return $this->googleApiClient->getAccessToken();
    }

    /**
     * Clear auth token
     */
    public function clearToken()
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
     * @return string
     */
    public function getAuthenticationUrl($state)
    {
        $this->googleApiClient->setState($state);

        return $this->googleApiClient->createAuthUrl();
    }

    /**
     * @return string|null
     */
    public function getChannelName()
    {
        $parts = 'id,brandingSettings';
        $opts = array('mine' => true);

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
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     * @return string
     */
    public function getStreamUrl(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube)
    {
        $this->getAccessToken($channelYouTube->getRefreshToken());
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        $youTubeId = $event->getYouTubeId();
        $youTubeBroadcast = $this->getExternalBroadcastById($youTubeId);

        if (!$youTubeBroadcast) {
            return;
        }

        $youTubeDetails = $youTubeBroadcast->getContentDetails();
        $streamId = $youTubeDetails->getBoundStreamId();

        $streamResponse = $this->getExternalStreamById($streamId);

        $streamAddress = $streamResponse->getCdn()->getIngestionInfo()->getIngestionAddress();
        $streamName = $streamResponse->getCdn()->getIngestionInfo()->getStreamName();

        return $streamAddress.'/'.$streamName;
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function createLiveEvent(LiveBroadcast $broadcast, ChannelYouTube $channel)
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
     * @param LiveBroadcast $broadcast
     * @param ChannelYouTube $channel
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, ChannelYouTube $channel)
    {
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
        $youTubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        $this->updateLiveStream($youTubeEvent);
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYouTube $channel
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, ChannelYouTube $channel)
    {
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
        $youTubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        $this->removeLivestream($youTubeEvent);
        $this->entityManager->remove($youTubeEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     * @return mixed
     */
    public function getBroadcastStatus(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube)
    {
        $this->getAccessToken($channelYouTube->getRefreshToken());

        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        $youTubeId = $event->getYouTubeId();

        $youTubeBroadcast = $this->getExternalBroadcastById($youTubeId);

        /** @var \Google_Service_YouTube_LiveBroadcastStatus $youTubeStatus */
        $youTubeStatus = $youTubeBroadcast->getStatus();

        return $youTubeStatus->getLifeCycleStatus();
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYouTube $channelYouTube
     * @param string $state
     */
    public function transitionState(LiveBroadcast $liveBroadcast, ChannelYouTube $channelYouTube, $state)
    {
        $this->getAccessToken($channelYouTube->getRefreshToken());

        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YouTubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYouTube);

        $youTubeId = $event->getYouTubeId();

        try {
            $this->youTubeApiClient->liveBroadcasts->transition($state, $youTubeId, 'status');
        } catch (\Google_Service_Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYouTube $channel
     * @param string $status
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    protected function setupLivestream(LiveBroadcast $liveBroadcast, ChannelYouTube $channel, $status = 'public')
    {
        $this->getAccessToken($channel->getRefreshToken());

        $broadcastResponse = $this->updateBroadcast($liveBroadcast, $status);
        $streamsResponse = $this->createStream($liveBroadcast->getName());

        // Bind Broadcast and Stream
        $bindBroadcastResponse = $this->youTubeApiClient->liveBroadcasts->bind(
            $broadcastResponse->getId(),
            'id,contentDetails',
            array('streamId' => $streamsResponse->getId())
        );

        return $bindBroadcastResponse;
    }

    /**
     * Edit a planned live event
     *
     * @param YouTubeEvent $event
     */
    protected function updateLiveStream(YouTubeEvent $event)
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
    protected function removeLivestream(YouTubeEvent $event)
    {
        $channel = $event->getChannel();
        $this->getAccessToken($channel->getRefreshToken());
        $this->youTubeApiClient->liveBroadcasts->delete($event->getYouTubeId());
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string $privacyStatus
     * @param null $id
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    protected function updateBroadcast(LiveBroadcast $liveBroadcast, $privacyStatus = 'public', $id = null)
    {
        $externalBroadcast = $this->setupBroadcast($liveBroadcast, $privacyStatus, $id);

        if ($id !== null) {
            return $this->youTubeApiClient->liveBroadcasts->update('snippet,status', $externalBroadcast);
        }

        return $this->youTubeApiClient->liveBroadcasts->insert('snippet,status', $externalBroadcast);
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param string $privacyStatus
     * @param null $id
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    protected function setupBroadcast(LiveBroadcast $liveBroadcast, $privacyStatus = 'public', $id = null)
    {
        $title = $liveBroadcast->getName();
        $description = $liveBroadcast->getDescription();
        $start = $liveBroadcast->getStartTimestamp();
        $end = $liveBroadcast->getEndTimestamp();

        if (new \DateTime() > $start) {
            $start = new \DateTime();
            $start->add(new \DateInterval('PT1S'));
        }

        $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($title);
        $broadcastSnippet->setDescription($description);
        $broadcastSnippet->setScheduledStartTime($start->format(\DateTime::ATOM));
        $broadcastSnippet->setScheduledEndTime($end->format(\DateTime::ATOM));

        $status = new \Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus($privacyStatus);

        $broadcastInsert = new \Google_Service_YouTube_LiveBroadcast();
        if ($id !== null) {
            $broadcastInsert->setId($id);
        }
        $broadcastInsert->setSnippet($broadcastSnippet);
        $broadcastInsert->setStatus($status);
        $broadcastInsert->setKind('youtube#liveBroadcast');

        return $broadcastInsert;
    }

    /**
     * @param $title
     * @return \Google_Service_YouTube_LiveStream
     */
    protected function createStream($title)
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
     * @return \Google_Service_YouTube_LiveBroadcast|null
     */
    protected function getExternalBroadcastById($youTubeId)
    {
        $broadcasts = $this->youTubeApiClient->liveBroadcasts->listLiveBroadcasts('status,contentDetails', array(
            'id' => $youTubeId,
        ))->getItems();

        if (!count($broadcasts)) {
            return null;
        }

        return $broadcasts[0];
    }

    /**
     * @param string $streamId
     * @return \Google_Service_YouTube_LiveStream|null
     */
    protected function getExternalStreamById($streamId)
    {
        $streamItems = $this->youTubeApiClient->liveStreams->listLiveStreams('snippet,cdn,status', array(
            'id' => $streamId,
        ))->getItems();

        if (!count($streamItems)) {
            return null;
        }

        return $streamItems[0];
    }
}
