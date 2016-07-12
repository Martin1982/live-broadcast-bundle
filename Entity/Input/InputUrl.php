<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Doctrine\ORM\Mapping as ORM;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InputUrl.
 *
 * @ORM\Table(name="broadcast_input_url", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class InputUrl extends BaseInput
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
     * @return InputUrl
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

    /**
     * @return string
     * @throws LiveBroadcastException
     */
    public function generateInputCmd()
    {
        $inputUrl = $this->getUrl();

        if (filter_var($inputUrl, FILTER_VALIDATE_URL) === false) {
            throw new LiveBroadcastException(sprintf('Invalid URL %s', $inputUrl));
        }

        return sprintf('-re -i %s', escapeshellarg($inputUrl));
    }
}
