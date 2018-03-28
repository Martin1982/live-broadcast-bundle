<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config;

/**
 * Class GoogleConfig
 */
class GoogleConfig
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * GoogleConfig constructor
     *
     * @param string|null $clientId
     * @param string|null $clientSecret
     */
    public function __construct(string $clientId = null, string $clientSecret = null)
    {
        $this->setClientId($clientId);
        $this->setClientSecret($clientSecret);
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     *
     * @return GoogleConfig
     */
    public function setClientId($clientId): GoogleConfig
    {
        $this->clientId = (string) $clientId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
     *
     * @return GoogleConfig
     */
    public function setClientSecret($clientSecret): GoogleConfig
    {
        $this->clientSecret = (string) $clientSecret;

        return $this;
    }
}
