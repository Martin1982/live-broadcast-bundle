<?php

namespace Martin1982\LiveBroadcastBundle\Tests\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\ThumbnailUploadListener;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadListenerTest
 * @package Martin1982\LiveBroadcastBundle\Tests\EventListener
 */
class ThumbnailUploadListenerTest extends TestCase
{
    /**
     * @var ThumbnailUploadService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploadService;

    /**
     * @var ThumbnailUploadListener
     */
    private $eventListener;

    /**
     *
     */
    public function setUp()
    {
        $this->uploadService = $this->createMock(ThumbnailUploadService::class);
        $this->eventListener = new ThumbnailUploadListener($this->uploadService);
    }

    /**
     * prePersist
     */
    public function testPrePersist()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'filename', null, null, UPLOAD_ERR_NO_FILE);
        $liveBroadcast->setThumbnail($uploadedFile);

        $this->uploadService->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn('new-filename');

        $args = new LifecycleEventArgs($liveBroadcast, $objectManager);
        $this->eventListener->prePersist($args);

        self::assertEquals('new-filename', $liveBroadcast->getThumbnail());
    }

    /**
     * preUpdate
     */
    public function testPreUpdate()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $uploadedFile = new UploadedFile('/tmp', 'thumbnail', null, null, UPLOAD_ERR_NO_FILE);
        $liveBroadcast->setThumbnail($uploadedFile);

        $this->uploadService->expects($this->once())
            ->method('upload')
            ->with($uploadedFile)
            ->willReturn('new-thumbnail');

        $changeSet = [];
        $args = new PreUpdateEventArgs($liveBroadcast, $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);

        self::assertEquals('new-thumbnail', $liveBroadcast->getThumbnail());
    }

    /**
     * preUpdate
     */
    public function testPreUpdateInvalidEntity()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $this->uploadService->expects($this->never())
            ->method('upload');

        $changeSet = [];
        $args = new PreUpdateEventArgs(new \stdClass(), $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);
    }

    /**
     * preUpdate
     */
    public function testPreUpdateNoChange()
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $liveBroadcast = new LiveBroadcast();

        $this->uploadService->expects($this->never())
            ->method('upload');

        $changeSet = ['thumbnail' => ['old_value']];
        $args = new PreUpdateEventArgs($liveBroadcast, $entityManager, $changeSet);
        $this->eventListener->preUpdate($args);

        self::assertEquals('old_value', $liveBroadcast->getThumbnail());
    }


    /**
     * postLoad
     */
    public function testPostLoad()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $liveBroadcast = new LiveBroadcast();

        $liveBroadcast->setThumbnail('thumbnail.jpg');

        $this->uploadService->expects($this->once())
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
    public function testPostLoadInvalidEntity()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->createMock(ObjectManager::class);
        $this->uploadService->expects($this->never())
            ->method('getTargetDirectory');

        $args = new LifecycleEventArgs(new \stdClass(), $objectManager);
        $this->eventListener->postLoad($args);
    }
}
