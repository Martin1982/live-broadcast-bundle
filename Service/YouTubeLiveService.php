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
     * @var Session
     */
    protected $session;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * YouTubeLiveService constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @param Session $session
     */
    public function __construct($clientId, $clientSecret, Session $session, RequestStack $requestStack, Router $router)
    {
        if (empty($clientId) || empty($clientSecret)) {
            throw new LiveBroadcastException('The YouTube oAuth settings are not correct.');
        }

        $this->session = $session;
        $this->requestStack = $requestStack;

        $redirectUri = $router->generate(
            'admin_martin1982_livebroadcast_channel_basechannel_youtubeoauth',
            array(),
            Router::ABSOLUTE_URL
        );

        $googleClient = new \Google_Client();
        $googleClient->setClientId($clientId);
        $googleClient->setClientSecret($clientSecret);
        $googleClient->setScopes('https://www.googleapis.com/auth/youtube');
        $googleClient->setRedirectUri($redirectUri);

        $this->googleClient = $googleClient;
        $this->youtubeApi = new \Google_Service_YouTube($googleClient);

        $this->clearTokenByRequest();
        $this->getCodeFromRequest();
        $this->setAccessTokenFromSession();
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
     * Get the authentication URL for the googleclient
     *
     * @return string
     */
    public function getAuthenticationUrl()
    {
        $state = mt_rand();
        $this->googleClient->setState($state);
        $this->session->set('state', $state);

        return $this->googleClient->createAuthUrl();
    }

    /**
     * Clear token by the 'cleartoken' request variable
     */
    protected function clearTokenByRequest()
    {
        if ($this->requestStack->getCurrentRequest()->get('cleartoken')) {
            $this->session->remove('token');
        }
    }

    /**
     * Get the authentication code from the request
     */
    protected function getCodeFromRequest()
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestCode = $request->get('code');
        $requestState = $request->get('state');
        $sessionState = $this->session->get('state');

        if (!$requestCode) {
            return;
        }

        if (strval($sessionState) !== strval($requestState)) {
            return;
        }

        $this->googleClient->authenticate($requestCode);
        $this->session->set('token', $this->googleClient->getAccessToken());
    }

    /**
     * Set the access token from the session
     */
    protected function setAccessTokenFromSession()
    {
        $sessionToken = $this->session->get('token');
        if (!$sessionToken) {
            return;
        }

        $this->googleClient->setAccessToken($sessionToken);
    }
}
