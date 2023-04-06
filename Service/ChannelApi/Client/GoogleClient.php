<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client;

use Google\Client;
use Google\Service\YouTube;
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
    public array $scope = [YouTube::YOUTUBE];

    /**
     * GoogleClient constructor.
     *
     * @param GoogleConfig          $config
     * @param GoogleRedirectService $redirect
     * @param LoggerInterface       $logger
     */
    public function __construct(protected GoogleConfig $config, protected GoogleRedirectService $redirect, protected LoggerInterface $logger)
    {
    }

    /**
     * @return Client
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException
     */
    public function getClient(): Client
    {
        $client = new Client();
        $client->setLogger($this->logger);
        $client->setClientId($this->config->getClientId());
        $client->setClientSecret($this->config->getClientSecret());
        $client->setScopes($this->scope);
        $client->setAccessType('offline');
        $client->setRedirectUri($this->redirect->getOAuthRedirectUrl());
        $client->setApprovalPrompt('force');
        $client->setPrompt('consent');

        return $client;
    }
}
