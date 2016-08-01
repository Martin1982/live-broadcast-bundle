<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;

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
     * YouTubeLiveService constructor.
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct($clientId, $clientSecret)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new LiveBroadcastException('The YouTube oAuth settings are not correct.');
        }

        $googleClient = new \Google_Client();
        $googleClient->setClientId($clientId);
        $googleClient->setClientSecret($clientSecret);
        $googleClient->setScopes('https://www.googleapis.com/auth/youtube');
        $this->googleClient = $googleClient;

        $youtubeApi = new \Google_Service_YouTube($googleClient);
        $this->youtubeApi = $youtubeApi;
    }

    public function getAccessToken()
    {
        $this->googleClient->getAccessToken();
    }

    public function getRefreshToken()
    {
        $this->googleClient->getRefreshToken();
    }
}
