<?php

namespace Martin1982\LiveBroadcastBundle\Streams\Output;

interface OutputInterface
{
    /**
     * Give the cmd string to start the stream
     *
     * @return string
     */
    public function generateOutputCmd();
}