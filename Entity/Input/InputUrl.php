<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class InputUrl
 * @package Martin1982\LiveBroadcastBundle\Entity\Input
 *
 * @ORM\Table(name="broadcast_input_url", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\Entity()
 */
class InputUrl extends BaseInput
{
    /**
     * @var string
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
     * @return InputUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }
}
