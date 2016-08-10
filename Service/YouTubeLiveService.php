<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Doctrine\ORM\EntityManager;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\YoutubeEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Psr\Log\LoggerInterface;

/**
 * Class YouTubeLiveService
 * @package Martin1982\LiveBroadcastBundle\Service
 */
class YouTubeLiveService
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
    protected $youtubeApiClient;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * YouTubeLiveService constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @throws LiveBroadcastException
     */
    public function __construct(
        $clientId,
        $clientSecret,
        EntityManager $entityManager,
        LoggerInterface $logger
    ) {
        if (empty($clientId) || empty($clientSecret)) {
            throw new LiveBroadcastException('The YouTube oAuth settings are not correct.');
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
        $this->youtubeApiClient = new \Google_Service_YouTube($googleApiClient);
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

        $channels = $this->youtubeApiClient->channels->listChannels($parts, $opts);

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
     * @param ChannelYoutube $channelYoutube
     * @return string
     */
    public function getStreamUrl(LiveBroadcast $liveBroadcast, ChannelYoutube $channelYoutube)
    {
        $this->getAccessToken($channelYoutube->getRefreshToken());
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYoutube);

        $youtubeId = $event->getYoutubeId();
        $youtubeBroadcast = $this->getExternalBroadcastById($youtubeId);

        if (!$youtubeBroadcast) {
            return;
        }

        /** @var \Google_Service_YouTube_LiveBroadcastContentDetails $youtubeSnippet */
        $youtubeDetails = $youtubeBroadcast->getContentDetails();
        $streamId = $youtubeDetails->getBoundStreamId();

        $streamResponse = $this->getExternalStreamById($streamId);

        $streamAddress = $streamResponse->getCdn()->getIngestionInfo()->getIngestionAddress();
        $streamName = $streamResponse->getCdn()->getIngestionInfo()->getStreamName();

        return $streamAddress.'/'.$streamName;
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function createLiveEvent(LiveBroadcast $broadcast, ChannelYoutube $channel)
    {
        $youtubeData = $this->setupLivestream($broadcast, $channel);

        $youtubeEvent = new YoutubeEvent();
        $youtubeEvent->setBroadcast($broadcast);
        $youtubeEvent->setChannel($channel);
        $youtubeEvent->setYoutubeId($youtubeData->getId());

        $this->entityManager->persist($youtubeEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYoutube $channel
     */
    public function updateLiveEvent(LiveBroadcast $broadcast, ChannelYoutube $channel)
    {
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
        $youtubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        $this->updateLiveStream($youtubeEvent);
    }

    /**
     * @param LiveBroadcast $broadcast
     * @param ChannelYoutube $channel
     */
    public function removeLiveEvent(LiveBroadcast $broadcast, ChannelYoutube $channel)
    {
        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
        $youtubeEvent = $eventRepository->findBroadcastingToChannel($broadcast, $channel);

        $this->removeLivestream($youtubeEvent);
        $this->entityManager->remove($youtubeEvent);
        $this->entityManager->flush();
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYoutube $channelYoutube
     * @return mixed
     */
    public function getStreamState(LiveBroadcast $liveBroadcast, ChannelYoutube $channelYoutube)
    {
        $this->getAccessToken($channelYoutube->getRefreshToken());

        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYoutube);

        $youtubeId = $event->getYoutubeId();

        $youtubeBroadcast = $this->getExternalBroadcastById($youtubeId);

        /** @var \Google_Service_YouTube_LiveBroadcastContentDetails $youtubeDetails */
        $youtubeDetails = $youtubeBroadcast->getContentDetails();
        $streamId = $youtubeDetails->getBoundStreamId();

        /** @var \Google_Service_YouTube_LiveStream $streamResponse */
        $streamResponse = $this->getExternalStreamById($streamId);

        /** @var \Google_Service_YouTube_LiveStreamStatus $status */
        $statusses = $streamResponse->getStatus();

        return $statusses->getStreamStatus();
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYoutube $channelYoutube
     * @param string $state
     */
    public function transitionState(LiveBroadcast $liveBroadcast, ChannelYoutube $channelYoutube, $state)
    {
        $this->getAccessToken($channelYoutube->getRefreshToken());

        $eventRepository = $this->entityManager->getRepository('LiveBroadcastBundle:Metadata\YoutubeEvent');
        $event = $eventRepository->findBroadcastingToChannel($liveBroadcast, $channelYoutube);

        $youtubeId = $event->getYoutubeId();

        $this->youtubeApiClient->liveBroadcasts->transition($state, $youtubeId, 'status');
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     * @param ChannelYoutube $channel
     * @param string $status
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    protected function setupLivestream(LiveBroadcast $liveBroadcast, ChannelYoutube $channel, $status = 'public')
    {
        $this->getAccessToken($channel->getRefreshToken());

        $broadcastResponse = $this->updateBroadcast($liveBroadcast, $status);
        $streamsResponse = $this->createStream($liveBroadcast->getName());

        // Bind Broadcast and Stream
        $bindBroadcastResponse = $this->youtubeApiClient->liveBroadcasts->bind(
            $broadcastResponse->getId(),
            'id,contentDetails',
            array('streamId' => $streamsResponse->getId())
        );

        return $bindBroadcastResponse;
    }

    /**
     * Edit a planned live event
     *
     * @param YoutubeEvent $event
     */
    protected function updateLiveStream(YoutubeEvent $event)
    {
        $channel = $event->getChannel();
        $this->getAccessToken($channel->getRefreshToken());
        $liveBroadcast = $event->getBroadcast();

        $broadcastResponse = $this->updateBroadcast($liveBroadcast, 'public', $event->getYoutubeId());
        $event->setYoutubeId($broadcastResponse->getId());

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }

    /**
     * Remove a planned live event on YouTube
     *
     * @param YoutubeEvent $event
     */
    protected function removeLivestream(YoutubeEvent $event)
    {
        $channel = $event->getChannel();
        $this->getAccessToken($channel->getRefreshToken());
        $this->youtubeApiClient->liveBroadcasts->delete($event->getYoutubeId());
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
            return $this->youtubeApiClient->liveBroadcasts->update('snippet,status', $externalBroadcast);
        }

        return $this->youtubeApiClient->liveBroadcasts->insert('snippet,status', $externalBroadcast);
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

        return $this->youtubeApiClient->liveStreams->insert('snippet,cdn', $streamInsert);
    }

    /**
     * @param string $youtubeId
     * @return \Google_Service_YouTube_LiveBroadcast|null
     */
    protected function getExternalBroadcastById($youtubeId)
    {
        $broadcasts = $this->youtubeApiClient->liveBroadcasts->listLiveBroadcasts('contentDetails', array(
            'id' => $youtubeId,
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
        $streamItems = $this->youtubeApiClient->liveStreams->listLiveStreams('snippet,cdn,status', array(
            'id' => $streamId,
        ))->getItems();

        if (!count($streamItems)) {
            return null;
        }

        return $streamItems[0];
    }
}
