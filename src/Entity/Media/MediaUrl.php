<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MediaUrl
 */
#[ORM\Table(name: 'broadcast_input_url', options: ['collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'])]
#[ORM\Entity]
class MediaUrl extends AbstractMedia
{
    /**
     * @var string|null
     *
     *
     */
    #[Assert\NotBlank]
    #[Assert\Url]
    #[ORM\Column(name: 'url', type: 'string', nullable: false)]
    protected ?string $url = null;

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     *
     * @return MediaUrl
     */
    public function setUrl(?string $url): MediaUrl
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
