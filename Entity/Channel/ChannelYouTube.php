<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChannelYouTube
 *
 * @ORM\Table(name="channel_youtube", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelYouTube extends AbstractChannel implements PlannedChannelInterface
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank
     */
    protected string $refreshToken = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="youtube_channel_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank
     */
    protected string $youTubeChannelName = '';

    /**
     * @return string|null
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $refreshToken
     *
     * @return ChannelYouTube
     */
    public function setRefreshToken(string $refreshToken): ChannelYouTube
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getYouTubeChannelName(): string
    {
        return $this->youTubeChannelName;
    }

    /**
     * @param string|null $youTubeChannelName
     *
     * @return ChannelYouTube
     */
    public function setYouTubeChannelName(string $youTubeChannelName): ChannelYouTube
    {
        $this->youTubeChannelName = $youTubeChannelName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function isEntityConfigured($configuration): bool
    {
        if (!array_key_exists('youtube', $configuration)) {
            return false;
        }

        $youTubeConfig = $configuration['youtube'];

        if (!array_key_exists('client_id', $youTubeConfig)) {
            return false;
        }

        if (!array_key_exists('client_secret', $youTubeConfig)) {
            return false;
        }

        if (!$youTubeConfig['client_id'] || !$youTubeConfig['client_secret']) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'YouTube';
    }
}
