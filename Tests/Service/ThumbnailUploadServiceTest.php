<?php

namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadServiceTest
 */
class ThumbnailUploadServiceTest extends TestCase
{
    /**
     * Test uploading a file
     */
    public function testUpload()
    {
        $file = $this->createMock(UploadedFile::class);
        $file->expects($this->once())
            ->method('guessExtension')
            ->willReturn('png');
        $file->expects($this->once())
            ->method('move')
            ->willReturn(true);

        $uploader = new ThumbnailUploadService('/test/dir');
        $file = $uploader->upload($file);

        self::assertNotEmpty($file);
    }

    /**
     * Test retrieving the target directory
     */
    public function testGetTargetDirectory()
    {
        $uploader = new ThumbnailUploadService('/some/dir');

        self::assertEquals('/some/dir', $uploader->getTargetDirectory());
    }
}
