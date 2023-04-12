<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\ThumbnailUploadListener;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadListenerTest
 */
class ThumbnailUploadListenerTest extends TestCase
{
    /**
     * @var ThumbnailUploadService|MockObject
     */
    private $uploadService;

    /**
     * @var ThumbnailUploadListener
     */
    private ThumbnailUploadListener $eventListener;

    /**
     *
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->uploadService = $this->createMock(ThumbnailUploadService::class);
        $this->eventListener = new ThumbnailUploadListener($this->uploadService);
    }

    /**
     * prePersist
     * @throws Exception
     * @throws Exception
     */
    public function testPrePersist(): void
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'filename', null, UPLOAD_ERR_NO_FILE);
        $liveBroadcast->setThumbnail($uploadedFile);

        $this->uploadService->expects(static::once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($this->createMock(File::class));

        $args = new PrePersistEventArgs($liveBroadcast, $manager);
        $this->eventListener->prePersist($args);

        self::assertInstanceOf(File::class, $liveBroadcast->getThumbnail());
    }

    /**
     * preUpdate
     * @throws Exception
     * @throws Exception
     */
    public function testPreUpdate(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'thumbnail', null, UPLOAD_ERR_NO_FILE);
        $liveBroadcast->setThumbnail($uploadedFile);

        $this->uploadService->expects(static::once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($this->createMock(File::class));

        $changeSet = [];
        $args = new PreUpdateEventArgs($liveBroadcast, $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);

        self::assertInstanceOf(File::class, $liveBroadcast->getThumbnail());
    }

    /**
     * preUpdate
     * @throws Exception
     */
    public function testPreUpdateInvalidEntity(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->uploadService->expects(static::never())
            ->method('upload');

        $changeSet = [];
        $args = new PreUpdateEventArgs(new stdClass(), $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);
    }

    /**
     * preUpdate
     * @throws Exception
     */
    public function testPreUpdateNoChange(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $this->uploadService->expects(static::never())
            ->method('upload');

        $changeSet = ['thumbnail' => ['old_value']];
        $args = new PreUpdateEventArgs($liveBroadcast, $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);

        self::assertEquals('old_value', $liveBroadcast->getThumbnail());
    }


    /**
     * postLoad
     * @throws Exception
     */
    public function testPostLoad(): void
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $liveBroadcast->setThumbnail('/tmp/dir/thumbnail.jpg');

        $args = new PostLoadEventArgs($liveBroadcast, $manager);
        $this->eventListener->postLoad($args);

        $file = $liveBroadcast->getThumbnail();

        self::assertInstanceOf(File::class, $file);
        self::assertEquals('thumbnail.jpg', $file->getFilename());
        self::assertEquals('/tmp/dir', $file->getPath());
    }

    /**
     * postLoad
     * @throws Exception
     */
    public function testPostLoadInvalidEntity(): void
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $this->uploadService->expects(static::never())
            ->method('getTargetDirectory');

        $args = new PostLoadEventArgs(new stdClass(), $manager);
        $this->eventListener->postLoad($args);
    }
}
