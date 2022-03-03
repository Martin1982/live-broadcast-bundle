<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config;

/**
 * Class YouTubeConfig
 */
class YouTubeConfig
{
    /**
     * @var string
     */
    protected string $host;

    /**
     * @var string
     */
    protected string $thumbnailDirectory;

    /**
     * YouTubeConfig constructor.
     *
     * @param string $host
     * @param string $thumbnailDirectory
     */
    public function __construct(string $host, string $thumbnailDirectory)
    {
        $this->host = $host;
        $this->thumbnailDirectory = $thumbnailDirectory;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getThumbnailDirectory(): string
    {
        return $this->thumbnailDirectory;
    }
}
