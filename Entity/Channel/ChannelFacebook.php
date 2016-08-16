<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ChannelFacebook
 * @package Martin1982\LiveBroadcastBundle\Entity\Channel
 *
 * @ORM\Table(name="channel_facebook", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class ChannelFacebook extends BaseChannel
{
    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=255, nullable=false)
     */
    protected $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="fb_entity_id", type="string", length=128, nullable=false)
     */
    protected $fbEntityId;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return ChannelFacebook
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getFbEntityId()
    {
        return $this->fbEntityId;
    }

    /**
     * @param string $fbEntityId
     *
     * @return ChannelFacebook
     */
    public function setFbEntityId($fbEntityId)
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
     */
    public static function isEntityConfigured($configuration)
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
