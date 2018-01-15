<?php

namespace Martin1982\LiveBroadcastBundle\EventListener;

use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ThumbnailUploadService;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadListener
 * @package Martin1982\LiveBroadcastBundle\EventListener
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
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->uploadFile($args->getEntity());
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->uploadFile($args->getEntity(), $args->getEntityChangeSet());
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof LiveBroadcast) {
            return;
        }

        $thumbnail = $entity->getThumbnail();

        if ($thumbnail !== null) {
            $entity->setThumbnail(
                new File($this->uploadService->getTargetDirectory().DIRECTORY_SEPARATOR.$thumbnail, false)
            );
        }
    }

    /**
     * @param LiveBroadcast|mixed $entity
     * @param array               $entityChangeset
     */
    private function uploadFile($entity, array $entityChangeset = [])
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
