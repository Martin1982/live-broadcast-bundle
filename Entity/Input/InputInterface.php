<?php

namespace Martin1982\LiveBroadcastBundle\Entity\Input;


interface InputInterface
{
    /**
     * @return string
     */
    public function generateInputCmd();
}
