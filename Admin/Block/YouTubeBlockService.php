<?php

namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class YouTubeBlockService
 * @package Martin1982\LiveBroadcastBundle\Admin\Block
 */
class YouTubeBlockService extends BaseBlockService
{
    /**
     * @var YouTubeLiveService
     */
    protected $youtubeLive;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * YouTubeBlockService constructor.
     * @param string $name
     * @param EngineInterface $templating
     * @param YouTubeLiveService $youtubeLive
     * @param RequestStack $requestStack
     * @param Router $router ,
     * @param string $redirectRoute
     */
    public function __construct(
        $name,
        EngineInterface $templating,
        YouTubeLiveService $youtubeLive,
        RequestStack $requestStack,
        Router $router,
        $redirectRoute
    ) {
        $this->youtubeLive = $youtubeLive;
        $this->requestStack = $requestStack;

        $redirectUri = $router->generate(
            $redirectRoute,
            array(),
            Router::ABSOLUTE_URL
        );
        $this->youtubeLive->initApiClients($redirectUri);

        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = $request->getSession();

        if ($refreshToken = $session->get('youtubeRefreshToken')) {
            $this->youtubeLive->getAccessToken($refreshToken);
        }

        $isAuthenticated = $this->youtubeLive->isAuthenticated();

        if (!$isAuthenticated) {
            $state = mt_rand();
            $session->set('state', $state);
            $session->set('authreferer', $request->getRequestUri());
        }

        return $this->renderResponse('LiveBroadcastBundle:Block:youtube_auth.html.twig', array(
            'isAuthenticated' => $isAuthenticated,
            'authUrl' => $isAuthenticated ? '#' : $this->youtubeLive->getAuthenticationUrl($state),
            'youtubeChannelName' => $session->get('youtubeChannelName'),
            'youtubeRefreshToken' => $session->get('youtubeRefreshToken'),
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ), $response);
    }
}
