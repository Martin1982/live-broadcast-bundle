<?php

namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use Martin1982\LiveBroadcastBundle\Service\YouTubeLiveService;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Templating\EngineInterface;

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
     * YouTubeBlockService constructor.
     * @param string $name
     * @param EngineInterface $templating
     * @param YouTubeLiveService $youtubeLive
     */
    public function __construct($name, EngineInterface $templating, YouTubeLiveService $youtubeLive)
    {
        $this->youtubeLive = $youtubeLive;
        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $isAuthenticated = $this->youtubeLive->isAuthenticated();

        return $this->renderResponse('LiveBroadcastBundle:Block:youtube_auth.html.twig', array(
            'isAuthenticated' => $isAuthenticated,
            'authUrl' => $isAuthenticated ? '#' : $this->youtubeLive->getAuthenticationUrl(),
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ), $response);
    }
}
