<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputFile;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamInputServiceTest
 */
class StreamInputServiceTest extends TestCase
{
    /**
     * Test getting the input interface class
     *
     * @throws LiveBroadcastInputException
     * @throws Exception
     * @throws Exception
     */
    public function testGetInputInterface(): void
    {
        $inputFile = $this->createMock(InputFile::class);
        $inputFile->expects(static::atLeastOnce())
            ->method('getMediaType')
            ->willReturn(MediaFile::class);

        $media = $this->createMock(MediaFile::class);

        $input = new StreamInputService();
        $input->addStreamInput($inputFile, 'file');

        $interface = $input->getInputInterface($media);
        self::assertInstanceOf(InputFile::class, $interface);
    }

    /**
     * Test that an exception is thrown when the interface class is unknown
     * @throws Exception
     */
    public function testNoInputInterfaceFound(): void
    {
        $this->expectException(LiveBroadcastInputException::class);
        $input = new StreamInputService();
        $media = $this->createMock(MediaFile::class);

        $input->getInputInterface($media);
    }
}
