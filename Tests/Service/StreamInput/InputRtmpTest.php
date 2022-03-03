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
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * Class InputRtmpTest
 */
class InputRtmpTest extends TestCase
{
    use PHPMock;

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

        $media = new MediaRtmp();
        $media->setRtmpAddress('rtmp://10.10.10.10/live/stream1');

        $this->serverAddress->setMedia($media);
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
        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\StreamInput', 'fsockopen');
        $exec->expects(static::once())
            ->with('10.10.10.10')
            ->willReturn(false);

        $this->serverAddress->generateInputCmd();
    }

    /**
     * Test that an input cmd is properly generated
     *
     * @throws LiveBroadcastInputException
     */
    public function testGenerateInputCmd(): void
    {
        $exec = $this->getFunctionMock('Martin1982\LiveBroadcastBundle\Service\StreamInput', 'fsockopen');
        $exec->expects(static::once())
            ->with('10.10.10.10')
            ->willReturn(true);

        $command = $this->serverAddress->generateInputCmd();
        self::assertEquals('-re -i \'rtmp://10.10.10.10/live/stream1\'', $command);
    }
}
