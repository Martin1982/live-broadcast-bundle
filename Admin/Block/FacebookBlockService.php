<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin\Block;

use Martin1982\LiveBroadcastBundle\Admin\ChannelAdmin;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\FacebookApiService;
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
    public function __construct($name, EngineInterface $templating, FacebookApiService $apiService, ChannelAdmin $admin)
    {
        $this->apiService = $apiService;
        $this->admin = $admin;

        parent::__construct($name, $templating);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @param Response|null         $response
     *
     * @return Response
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        return $this->renderResponse(
            'LiveBroadcastBundle:Block:facebook_auth.html.twig',
            [
                'facebookAppId' => $this->apiService->getAppId(),
                'admin' => $this->admin,
            ],
            $response
        );
    }
}
