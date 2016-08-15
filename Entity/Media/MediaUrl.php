<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MediaUrl
 * @package Martin1982\LiveBroadcastBundle\Entity\Media
 *
 * @ORM\Table(name="broadcast_input_url", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class MediaUrl extends BaseMedia
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Url()
     *
     * @ORM\Column(name="url", type="string", length=128, nullable=false)
     */
    protected $url;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return MediaUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get input string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUrl();
    }
}
