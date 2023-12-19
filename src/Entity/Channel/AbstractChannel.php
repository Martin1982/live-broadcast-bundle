<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Martin1982\LiveBroadcastBundle\Validator\Constraints as BroadcastAssert;

/**
 * Class AbstractChannel
 *
 *
 * @BroadcastAssert\CanStreamToChannel
 */
#[ORM\Table(name: 'channel', options: ['collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'])]
#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
abstract class AbstractChannel
{
    /**
     * @var int|null
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $channelId = null;

    /**
     * @var string|null
     *
     *
     */
    #[ORM\Column(name: 'name', type: 'string', length: 128, nullable: false)]
    #[Assert\NotBlank]
    protected ?string $channelName = null;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_healthy', type: 'boolean', options: ['default' => 0])]
    protected bool $isHealthy = false;

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
     * @param string|null $channelName
     *
     * @return $this
     */
    public function setChannelName(?string $channelName): self
    {
        $this->channelName = $channelName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHealthy(): bool
    {
        return $this->isHealthy;
    }

    /**
     * @param bool $isHealthy
     *
     * @return AbstractChannel
     */
    public function setIsHealthy(bool $isHealthy): AbstractChannel
    {
        $this->isHealthy = $isHealthy;

        return $this;
    }

    /**
     * @param mixed $configuration
     *
     * @return bool
     */
    public static function isEntityConfigured(mixed $configuration): bool
    {
        if ($configuration) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s: %s', $this->getTypeName(), $this->getChannelName());
    }
}
