<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadListener
 */
class ThumbnailUploadListener
{
    /**
     * ThumbnailUploadListener constructor.
     * @param ThumbnailUploadService $uploadService
     */
    public function __construct(private ThumbnailUploadService $uploadService)
    {
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->uploadFile($args->getObject());
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->uploadFile($args->getObject(), $args->getEntityChangeSet());
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof LiveBroadcast) {
            return;
        }

        $thumbnail = (string) $entity->getThumbnail();

        if ('' !== $thumbnail) {
            $entity->setThumbnail(
                new File($thumbnail, false)
            );
        }
    }

    /**
     * @param LiveBroadcast|mixed $entity
     * @param array               $entityChangeSet
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    private function uploadFile($entity, array $entityChangeSet = []): void
    {
        if (!$entity instanceof LiveBroadcast) {
            return;
        }

        $file = $entity->getThumbnail();

        if (!$file instanceof UploadedFile) {
            // Keep current value when no new file is uploaded
            if (array_key_exists('thumbnail', $entityChangeSet)) {
                $entity->setThumbnail($entityChangeSet['thumbnail'][0]);
            }

            return;
        }

        $uploadedFile = $this->uploadService->upload($file);
        $entity->setThumbnail($uploadedFile);
    }
}
