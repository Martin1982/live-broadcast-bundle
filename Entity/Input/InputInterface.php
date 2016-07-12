<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;

/**
 * Interface InputInterface
  */
interface InputInterface
{
    /**
     * @return string
     */
    public function generateInputCmd();
}
