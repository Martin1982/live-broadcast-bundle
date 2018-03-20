<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
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
    private $serverAddress;

    /**
     *
     */
    public function setUp()
    {
        $this->serverAddress = new InputRtmp();

        $media = new MediaRtmp();
        $media->setRtmpAddress('rtmp://10.10.10.10/live/stream1');

        $this->serverAddress->setMedia($media);
    }

    /**
     *
     */
    public function testMediaType(): void
    {
        self::assertEquals(MediaRtmp::class, $this->serverAddress->getMediaType());
    }
}
