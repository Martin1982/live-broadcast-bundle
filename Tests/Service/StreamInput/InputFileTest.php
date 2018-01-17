<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputFile;
use PHPUnit\Framework\TestCase;

/**
 * Class InputFileTest
 * @package Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput
 */
class InputFileTest extends TestCase
{
    /**
     * @var InputFile
     */
    private $file;

    /**
     *
     */
    public function setUp()
    {
        $this->file = new InputFile();

        $media = new MediaFile();
        $media->setFileLocation('/does/not/exist');

        $this->file->setMedia($media);
    }

    /**
     * @expectedException \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     *
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmdInvalidFile()
    {
        $this->file->generateInputCmd();
    }


    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmd()
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'inputTest';
        file_put_contents($file, 'data');

        $mediaFile = new MediaFile();
        $mediaFile->setFileLocation($file);
        $this->file->setMedia($mediaFile);

        self::assertEquals('-re -i \''.$file.'\'', $this->file->generateInputCmd());
        unlink($file);
    }

    /**
     *
     */
    public function testMediaType()
    {
        self::assertEquals(MediaFile::class, $this->file->getMediaType());
    }
}
