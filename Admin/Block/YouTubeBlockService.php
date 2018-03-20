<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use http\Env\Request;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\GoogleClient;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class YouTubeBlockService
 */
class YouTubeBlockService extends AbstractBlockService
{
    /**
     * @var GoogleClient
     */
    protected $googleClient;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * YouTubeBlockService constructor
     *
     * @param string          $name
     * @param EngineInterface $templating
     * @param GoogleClient    $googleClient
     * @param RequestStack    $requestStack
     */
    public function __construct($name, EngineInterface $templating, GoogleClient $googleClient, RequestStack $requestStack)
    {
        $this->googleClient = $googleClient;
        $this->requestStack = $requestStack;

        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null         $response
     *
     * @return Response
     *
     * @throws LiveBroadcastException
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $client = $this->googleClient->getClient();
        if (!$client instanceof \Google_Client) {
            throw new LiveBroadcastException('Could not load the google client');
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            $request = new Request();
        }

        $session = $request->getSession();
        if (!$session) {
            $request->setSession(new Session());
        }

        $refreshToken = $session->get('youTubeRefreshToken');
        if ($refreshToken) {
            $client->fetchAccessTokenWithRefreshToken($refreshToken);
        }

        $accessToken = $client->getAccessToken();
        $isAuthenticated = (bool) $accessToken;
        $state = mt_rand();

        if (!$isAuthenticated) {
            $session->set('state', $state);
            $session->set('authreferer', $request->getRequestUri());
        }

        $client->setState($state);

        return $this->renderResponse(
            'LiveBroadcastBundle:Block:youtube_auth.html.twig',
            [
                'isAuthenticated' => $isAuthenticated,
                'authUrl' => $isAuthenticated ? '#' : $client->createAuthUrl(),
                'youTubeChannelName' => $session->get('youTubeChannelName'),
                'youTubeRefreshToken' => $session->get('youTubeRefreshToken'),
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
                ],
            $response
        );
    }
}
