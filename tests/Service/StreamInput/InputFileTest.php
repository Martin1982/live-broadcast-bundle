<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service\StreamInput;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastInputException;
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
    private InputFile $file;

    /**
     *
     */
    public function setUp(): void
    {
        $this->file = new InputFile();

        $media = new MediaFile();
        $media->setFileLocation('/does/not/exist');

        $this->file->setMedia($media);
    }

    /**
     * Test that cmd gets an invalid input file
     */
    public function testGenerateInputCmdInvalidFile(): void
    {
        $this->expectException(LiveBroadcastInputException::class);
        $this->file->generateInputCmd();
    }


    /**
     * Test generating the input command
     *
     * @throws LiveBroadcastInputException
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
     * Test the type of media
     */
    public function testMediaType(): void
    {
        self::assertEquals(MediaFile::class, $this->file->getMediaType());
    }
}
