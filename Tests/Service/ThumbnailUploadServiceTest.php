<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\Service;

use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadServiceTest
 */
class ThumbnailUploadServiceTest extends TestCase
{
    /**
     * Test uploading a file
     */
    public function testUpload(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->expects(static::once())
            ->method('guessExtension')
            ->willReturn('png');
        $file->expects(static::once())
            ->method('move')
            ->willReturn($this->createMock(File::class));

        $uploader = new ThumbnailUploadService('/test/dir');
        $file = $uploader->upload($file);

        self::assertNotEmpty($file);
    }

    /**
     * Test retrieving the target directory
     */
    public function testGetTargetDirectory(): void
    {
        $uploader = new ThumbnailUploadService('/some/dir');

        self::assertEquals('/some/dir', $uploader->getTargetDirectory());
    }
}
