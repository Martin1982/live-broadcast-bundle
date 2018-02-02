<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputFile;
use Martin1982\LiveBroadcastBundle\Service\StreamInputService;

/**
 * Class StreamInputServiceTest
 */
class StreamInputServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getting the input interface class
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGetInputInterface()
    {
        $inputFile = $this->createMock(InputFile::class);
        $inputFile->expects($this->any())
            ->method('getMediaType')
            ->willReturn('\Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile');

        $media = $this->createMock(MediaFile::class);

        $input = new StreamInputService();
        $input->addStreamInput($inputFile, 'file');

        $interface = $input->getInputInterface($media);
        self::assertInstanceOf(InputFile::class, $interface);
    }

    /**
     * Test that an exception is thrown when the interface class is unknown
     *
     * @expectedException  \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testNoInputInterfaceFound()
    {
        $input = new StreamInputService();
        $media = $this->createMock(MediaFile::class);

        $input->getInputInterface($media);
    }
}
