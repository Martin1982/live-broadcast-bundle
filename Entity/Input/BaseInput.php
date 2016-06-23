<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseInput.
 *
 * @ORM\Entity()
 * @ORM\Table(name="broadcast_input", options={"collate"="utf8mb4_general_ci", "charset"="utf8mb4"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
abstract class BaseInput
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $inputId;

    /**
     * @return int
     */
    public function getInputId()
    {
        return $this->inputId;
    }
}
