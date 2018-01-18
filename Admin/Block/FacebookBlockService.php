<?php

namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use Martin1982\LiveBroadcastBundle\Admin\ChannelAdmin;
use Martin1982\LiveBroadcastBundle\Service\FacebookApiService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class FacebookBlockService
 */
class FacebookBlockService extends AbstractBlockService
{
    /**
     * @var FacebookApiService
     */
    protected $apiService;

    /**
     * @var ChannelAdmin
     */
    protected $admin;

    /**
     * YouTubeBlockService constructor
     *
     * @param string             $name
     * @param EngineInterface    $templating
     * @param FacebookApiService $apiService
     * @param ChannelAdmin       $admin
     */
    public function __construct(
        $name,
        EngineInterface $templating,
        FacebookApiService $apiService,
        ChannelAdmin $admin
    ) {
        $this->apiService = $apiService;
        $this->admin = $admin;

        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null $response
     *
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse(
            'LiveBroadcastBundle:Block:facebook_auth.html.twig',
            [
                'facebookAppId' => $this->apiService->getAppId(),
                'admin' => $this->admin
            ],
            $response
        );
    }
}
