<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaMonitorStream;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputMonitorStream;
use PHPUnit\Framework\TestCase;

/**
 * Class InputMonitorStreamTest
 */
class InputMonitorStreamTest extends TestCase
{
    /**
     * @var InputMonitorStream
     */
    private $monitorStream;

    /**
     *
     */
    public function setUp()
    {
        $this->monitorStream = new InputMonitorStream();

        $media = new MediaMonitorStream();
        $media->setMonitorImage('/does/not/exist');

        $this->monitorStream->setMedia($media);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmdInvalidMonitorStream()
    {
        $this->monitorStream->generateInputCmd();
    }


    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmd()
    {
        $monitorFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'monitorStream';

        $monitorImage = new MediaMonitorStream();
        $monitorImage->setMonitorImage($monitorFile);
        $this->monitorStream->setMedia($monitorImage);

        file_put_contents($monitorFile, '');

        self::assertEquals(
            '-re -f lavfi -i anullsrc=r=48000 -r 1 -loop 1 -i \''.$monitorFile.'\'',
            $this->monitorStream->generateInputCmd()
        );

        unlink($monitorFile);
    }

    /**
     *
     */
    public function testMediaType()
    {
        self::assertEquals(MediaMonitorStream::class, $this->monitorStream->getMediaType());
    }
}
