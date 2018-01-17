<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class InputUrlTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput
 */
class InputUrlTest extends TestCase
{
    /**
     * @var InputUrl
     */
    private $inputUrl;

    /**
     *
     */
    public function setUp()
    {
        $this->inputUrl = new InputUrl();

        $media = new MediaUrl();
        $media->setUrl('http://live.broadcast.com');
        $this->inputUrl->setMedia($media);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmdInvalidUrl()
    {
        $media = new MediaUrl();
        $media->setUrl('invalid_url');
        $this->inputUrl->setMedia($media);

        $this->inputUrl->generateInputCmd();
    }


    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmd()
    {
        self::assertEquals('-re -i \'http://live.broadcast.com\'', $this->inputUrl->generateInputCmd());
    }

    /**
     *
     */
    public function testMediaType()
    {
        self::assertEquals(MediaUrl::class, $this->inputUrl->getMediaType());
    }
}
