<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelFacebook
 *
 * @ORM\Table(name="channel_facebook", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelFacebook extends AbstractChannel implements PlanableChannelInterface
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="access_token", type="string", length=255, nullable=false)
     */
    protected $accessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="fb_entity_id", type="string", length=128, nullable=false)
     */
    protected $fbEntityId;

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return ChannelFacebook
     */
    public function setAccessToken($accessToken): ChannelFacebook
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFbEntityId(): ?string
    {
        return $this->fbEntityId;
    }

    /**
     * @param string $fbEntityId
     *
     * @return ChannelFacebook
     */
    public function setFbEntityId($fbEntityId): ChannelFacebook
    {
        $this->fbEntityId = $fbEntityId;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'Facebook: '.$this->getChannelName();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public static function isEntityConfigured($configuration): bool
    {
        if (!array_key_exists('facebook', $configuration)) {
            return false;
        }

        $facebookConfig = $configuration['facebook'];

        if (!array_key_exists('application_id', $facebookConfig)) {
            return false;
        }

        if (!array_key_exists('application_secret', $facebookConfig)) {
            return false;
        }

        if (!$facebookConfig['application_id'] || !$facebookConfig['application_secret']) {
            return false;
        }

        return true;
    }
}
