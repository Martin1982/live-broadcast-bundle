<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client;

use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\GoogleConfig;
use Martin1982\LiveBroadcastBundle\Service\GoogleRedirectService;
use Psr\Log\LoggerInterface;

/**
 * Class GoogleClient
 */
class GoogleClient
{
    /**
     * @var array
     */
    public $scope = ['https://www.googleapis.com/auth/youtube'];

    /**
     * @var GoogleConfig
     */
    protected $config;

    /**
     * @var GoogleRedirectService
     */
    protected $redirect;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Google_Client|null
     */
    protected $googleClient;

    /**
     * GoogleClient constructor.
     *
     * @param GoogleConfig          $config
     * @param GoogleRedirectService $redirect
     * @param LoggerInterface       $logger
     */
    public function __construct(GoogleConfig $config, GoogleRedirectService $redirect, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->redirect = $redirect;
        $this->logger = $logger;
    }

    /**
     * @return \Google_Client|null
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function getClient(): ?\Google_Client
    {
        if (!$this->googleClient) {
            $this->setupClient();
        }

        return $this->googleClient;
    }

    /**
     * Setup the Google API client
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    protected function setupClient(): void
    {
        $client = new \Google_Client();
        $client->setLogger($this->logger);
        $client->setClientId($this->config->getClientId());
        $client->setClientSecret($this->config->getClientSecret());
        $client->setScopes($this->scope);
        $client->setAccessType('offline');
        $client->setRedirectUri($this->redirect->getOAuthRedirectUrl());
        $client->setApprovalPrompt('force');

        $this->googleClient = $client;
    }
}
