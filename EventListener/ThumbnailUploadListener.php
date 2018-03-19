<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadListener
 */
class ThumbnailUploadListener
{
    /**
     * @var ThumbnailUploadService
     */
    private $uploadService;

    /**
     * ThumbnailUploadListener constructor.
     * @param ThumbnailUploadService $uploadService
     */
    public function __construct(ThumbnailUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->uploadFile($args->getEntity());
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->uploadFile($args->getEntity(), $args->getEntityChangeSet());
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof LiveBroadcast) {
            return;
        }

        $thumbnail = $entity->getThumbnail();

        if (null !== $thumbnail) {
            $entity->setThumbnail(
                new File($this->uploadService->getTargetDirectory().DIRECTORY_SEPARATOR.$thumbnail, false)
            );
        }
    }

    /**
     * @param LiveBroadcast|mixed $entity
     * @param array               $entityChangeset
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    private function uploadFile($entity, array $entityChangeset = []): void
    {
        if (!$entity instanceof LiveBroadcast) {
            return;
        }

        $file = $entity->getThumbnail();

        if (!$file instanceof UploadedFile) {
            // Keep current value when no new file is uploaded
            if (array_key_exists('thumbnail', $entityChangeset)) {
                $entity->setThumbnail($entityChangeset['thumbnail'][0]);
            }

            return;
        }

        $fileName = $this->uploadService->upload($file);
        $entity->setThumbnail($fileName);
    }
}
