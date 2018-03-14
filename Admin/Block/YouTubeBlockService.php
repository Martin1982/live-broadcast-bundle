<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\YouTubeApiService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Class YouTubeBlockService
 */
class YouTubeBlockService extends AbstractBlockService
{
    /**
     * @var YouTubeApiService
     */
    protected $youTubeApi;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * YouTubeBlockService constructor
     *
     * @param string                $name
     * @param EngineInterface       $templating
     * @param YouTubeApiService     $youTubeApi
     * @param RequestStack          $requestStack
     * @param GoogleRedirectService $redirectService
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function __construct($name, EngineInterface $templating, YouTubeApiService $youTubeApi, RequestStack $requestStack, GoogleRedirectService $redirectService)
    {
        $this->youTubeApi = $youTubeApi;
        $this->requestStack = $requestStack;

        $redirectUri = $redirectService->getOAuthRedirectUrl();
        $this->youTubeApi->initApiClients($redirectUri);

        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null         $response
     *
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        if ($refreshToken = $session->get('youTubeRefreshToken')) {
            $this->youTubeApi->getAccessToken($refreshToken);
        }

        $isAuthenticated = $this->youTubeApi->isAuthenticated();
        $state = mt_rand();

        if (!$isAuthenticated) {
            $session->set('state', $state);
            $session->set('authreferer', $request->getRequestUri());
        }

        return $this->renderResponse(
            'LiveBroadcastBundle:Block:youtube_auth.html.twig',
            [
                'isAuthenticated' => $isAuthenticated,
                'authUrl' => $isAuthenticated ? '#' : $this->youTubeApi->getAuthenticationUrl($state),
                'youTubeChannelName' => $session->get('youTubeChannelName'),
                'youTubeRefreshToken' => $session->get('youTubeRefreshToken'),
                'block' => $blockContext->getBlock(),
                'settings' => $blockContext->getSettings(),
                ],
            $response
        );
    }
}
