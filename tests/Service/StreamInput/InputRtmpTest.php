<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputRtmp;
use PHPUnit\Framework\TestCase;

/**
 * Class InputRtmpTest
 */
class InputRtmpTest extends TestCase
{
    /**
     * @var InputRtmp
     */
    private InputRtmp $serverAddress;

    /**
     * Setup a basic RTMP object
     */
    public function setUp(): void
    {
        $this->serverAddress = new InputRtmp();
    }

    /**
     * Test that the media type is RTMP
     */
    public function testMediaType(): void
    {
        self::assertEquals(MediaRtmp::class, $this->serverAddress->getMediaType());
    }

    /**
     * Test that an input cmd cannot be generated
     */
    public function testCannotGenerateInputCmd(): void
    {
        $this->expectException(LiveBroadcastInputException::class);

        $media = new MediaRtmp();
        $media->setRtmpAddress('rxp');

        $this->serverAddress->setMedia($media);
        $this->serverAddress->generateInputCmd();
    }
}
