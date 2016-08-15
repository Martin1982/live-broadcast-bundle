<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Entity\Media;

use Martin1982\LiveBroadcastBundle\Entity\Media\InputInterface;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;

/**
 * Class MediaUrlTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Entity\Media
 */
class MediaUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getUrl method
     */
    public function testGetUrl()
    {
        $input = new MediaUrl();
        self::assertEquals('', $input->getUrl());

        $input->setUrl('http://www.google.com');
        self::assertEquals('http://www.google.com', $input->getUrl());
    }

    /**
     * Test the __toString method
     */
    public function testToString()
    {
        $input = new MediaUrl();
        self::assertEquals('', (string) $input);

        $input->setUrl('https://github.com/Martin1982/live-broadcast-bundle');
        self::assertEquals('https://github.com/Martin1982/live-broadcast-bundle', $input->getUrl());
    }
}
