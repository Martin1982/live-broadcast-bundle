<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Service\StreamInput\InputFile;
use PHPUnit\Framework\TestCase;

/**
 * Class InputFileTest
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
    public function testGenerateInputCmdInvalidFile(): void
    {
        $this->file->generateInputCmd();
    }


    /**
     * @throws \Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException
     */
    public function testGenerateInputCmd(): void
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
    public function testMediaType(): void
    {
        self::assertEquals(MediaFile::class, $this->file->getMediaType());
    }
}
