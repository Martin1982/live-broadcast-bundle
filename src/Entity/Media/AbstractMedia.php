<?php

declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */

namespace Martin1982\LiveBroadcastBundle\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class AbstractMedia
 */
#[ORM\Table(name: 'broadcast_input', options: ['collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'])]
#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
abstract class AbstractMedia
{
    /**
     * @var int|null
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $inputId = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->getInputId();
    }

    /**
     * @return int|null
     */
    public function getInputId(): ?int
    {
        return $this->inputId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getInputId();
    }
}
