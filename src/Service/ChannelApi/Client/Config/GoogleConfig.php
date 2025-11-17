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
     * GoogleConfig constructor
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(protected string $clientId, protected string $clientSecret)
    {
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
    public function setClientId(mixed $clientId): GoogleConfig
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
    public function setClientSecret(mixed $clientSecret): GoogleConfig
    {
        $this->clientSecret = (string) $clientSecret;

        return $this;
    }
}
