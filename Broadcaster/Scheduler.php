<?php

namespace Martin1982\LiveBroadcastBundle\Broadcaster;

class Scheduler
{
    public function testPrerequisites()
    {
        var_dump($this->hasFFMpeg());
    }

    public function checkRunningBroadcasts()
    {
        $this->testPrerequisites();
    }

    public function startBroadcast()
    {

    }

    public function stopBroadcast()
    {

    }

    protected function hasFFMpeg()
    {
        $cmdOutput = array();
        exec('ffmpeg -version', $cmdOutput);
        if (false !== strpos($cmdOutput[0], 'ffmpeg version')) {
            return true;
        }

        return false;
    }
}