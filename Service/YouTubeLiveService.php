<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class YouTubeLiveService
 * @package Martin1982\LiveBroadcastBundle\Service
 */
class YouTubeLiveService
{
    /**
     * @var \Google_Client
     */
    protected $googleClient;

    /**
     * @var \Google_Service_YouTube
     */
    protected $youtubeApi;

    /**
     * @var string
     */
    protected $streamUrl;

    /**
     * YouTubeLiveService constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param Router $router
     */
    public function __construct($clientId, $clientSecret, Router $router)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new LiveBroadcastException('The YouTube oAuth settings are not correct.');
        }

        $redirectUri = $router->generate(
            'admin_martin1982_livebroadcast_channel_basechannel_youtubeoauth',
            array(),
            Router::ABSOLUTE_URL
        );

        $googleClient = new \Google_Client();
        $googleClient->setClientId($clientId);
        $googleClient->setClientSecret($clientSecret);
        $googleClient->setScopes('https://www.googleapis.com/auth/youtube');
        $googleClient->setAccessType('offline');
        $googleClient->setRedirectUri($redirectUri);

        $this->googleClient = $googleClient;
        $this->youtubeApi = new \Google_Service_YouTube($googleClient);
    }

    /**
     * @param $refreshToken
     * @return array
     */
    public function getAccessToken($refreshToken)
    {
        $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);

        return $this->googleClient->getAccessToken();
    }

    /**
     * Set the access token
     * @param string $sessionToken
     */
    public function setAccessToken($sessionToken)
    {
        $this->googleClient->setAccessToken($sessionToken);
    }

    /**
     * Check if the client has authenticated the user
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return (bool) $this->googleClient->getAccessToken();
    }

    /**
     * @param $requestCode
     * @param $requestState
     * @param $sessionState
     * @return array|void
     */
    public function authenticate($requestCode, $requestState, $sessionState)
    {
        if (strval($sessionState) !== strval($requestState)) {
            return;
        }

        $this->googleClient->authenticate($requestCode);

        return $this->googleClient->getAccessToken();
    }

    /**
     * Clear auth token
     */
    public function clearToken()
    {
        $this->googleClient->revokeToken();
    }

    /**
     * Retrieve the client's refresh token
     *
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->googleClient->getRefreshToken();
    }

    /**
     * Get the authentication URL for the googleclient
     *
     * @param string $state
     * @return string
     */
    public function getAuthenticationUrl($state)
    {
        $this->googleClient->setState($state);
        return $this->googleClient->createAuthUrl();
    }

    public function getChannelName()
    {
        $parts = "id,brandingSettings";
        $opts = array("mine" => true);

        $channels = $this->youtubeApi->channels->listChannels($parts, $opts);

        if ($channels->count()) {
            /** @var \Google_Service_YouTube_Channel $channel */
            $channel = $channels->getItems()[0];

            /** @var \Google_Service_YouTube_ChannelBrandingSettings $branding */
            $branding = $channel->getBrandingSettings();

            return $branding->getChannel()->title;
        }
    }

    /**
     * @return string
     */
    public function getStreamUrl()
    {
        return $this->streamUrl;
    }

    /**
     * @param $title
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $status
     */
    public function setupLivestream($title, \DateTime $start, \DateTime $end, $status = 'public')
    {
        $broadcastResponse = $this->createBroadcast($title, $start, $end, $status);
        $streamsResponse = $this->createStream($title);

        // Bind Broadcast and Stream
        $bindBroadcastResponse = $this->youtubeApi->liveBroadcasts->bind(
            $broadcastResponse->getId(),
            'id,contentDetails',
            array('streamId' => $streamsResponse->getId())
        );

        $this->streamUrl = $streamsResponse->getCdn()->getIngestionInfo()->getIngestionAddress();
    }

    /**
     * @param $title
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $status
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    protected function createBroadcast($title, \DateTime $start, \DateTime $end, $status = 'public')
    {
        $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($title);
        $broadcastSnippet->setScheduledStartTime($start);
        $broadcastSnippet->setScheduledEndTime($end);

        $status = new \Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus($status);

        $broadcastInsert = new \Google_Service_YouTube_LiveBroadcast();
        $broadcastInsert->setSnippet($broadcastSnippet);
        $broadcastInsert->setStatus($status);
        $broadcastInsert->setKind('youtube#liveBroadcast');

        return $this->youtubeApi->liveBroadcasts->insert('snippet,status', $broadcastInsert);
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
        $cdn->setFormat('1080p');
        $cdn->setIngestionType('rtmp');

        $streamInsert = new \Google_Service_YouTube_LiveStream();
        $streamInsert->setSnippet($streamSnippet);
        $streamInsert->setCdn($cdn);
        $streamInsert->setKind('youtube#liveStream');

        return $this->youtubeApi->liveStreams->insert('snippet,cdn', $streamInsert);
    }
}
