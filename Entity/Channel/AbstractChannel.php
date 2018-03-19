<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractChannel
 *
 * @ORM\Entity()
 * @ORM\Table(name="channel", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
abstract class AbstractChannel
{
    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $channelId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    protected $channelName;

    /**
     * @return int|null
     */
    public function getChannelId(): ?int
    {
        return $this->channelId;
    }

    /**
     * @return string|null
     */
    public function getChannelName(): ?string
    {
        return $this->channelName;
    }

    /**
     * @param string $channelName
     *
     * @return $this
     */
    public function setChannelName($channelName): self
    {
        $this->channelName = $channelName;

        return $this;
    }

    /**
     * @param mixed $configuration
     *
     * @return bool
     */
    public static function isEntityConfigured($configuration): bool
    {
        if ($configuration) {
            return true;
        }

        return true;
    }
}
