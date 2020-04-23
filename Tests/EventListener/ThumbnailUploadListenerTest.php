<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\ThumbnailUploadListener;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
    private $eventListener;

    /**
     *
     */
    public function setUp(): void
    {
        $this->uploadService = $this->createMock(ThumbnailUploadService::class);
        $this->eventListener = new ThumbnailUploadListener($this->uploadService);
    }

    /**
     * prePersist
     */
    public function testPrePersist(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'filename', null, null, UPLOAD_ERR_NO_FILE);
        $liveBroadcast->setThumbnail($uploadedFile);

        $this->uploadService->expects(static::once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn($this->createMock(File::class));

        $args = new LifecycleEventArgs($liveBroadcast, $objectManager);
        $this->eventListener->prePersist($args);

        self::assertInstanceOf(File::class, $liveBroadcast->getThumbnail());
    }

    /**
     * preUpdate
     */
    public function testPreUpdate(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'thumbnail', null, null, UPLOAD_ERR_NO_FILE);
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
     */
    public function testPreUpdateInvalidEntity(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->uploadService->expects(static::never())
            ->method('upload');

        $changeSet = [];
        $args = new PreUpdateEventArgs(new \stdClass(), $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);
    }

    /**
     * preUpdate
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
     */
    public function testPostLoad(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $liveBroadcast = new LiveBroadcast();

        $liveBroadcast->setThumbnail('thumbnail.jpg');

        $this->uploadService->expects(static::once())
            ->method('getTargetDirectory')
            ->willReturn('/tmp/dir');

        $args = new LifecycleEventArgs($liveBroadcast, $objectManager);
        $this->eventListener->postLoad($args);

        $file = $liveBroadcast->getThumbnail();

        self::assertInstanceOf(File::class, $file);
        self::assertEquals('thumbnail.jpg', $file->getFilename());
        self::assertEquals('/tmp/dir', $file->getPath());
    }

    /**
     * postLoad
     */
    public function testPostLoadInvalidEntity(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $this->uploadService->expects(static::never())
            ->method('getTargetDirectory');

        $args = new LifecycleEventArgs(new \stdClass(), $objectManager);
        $this->eventListener->postLoad($args);
    }
}
