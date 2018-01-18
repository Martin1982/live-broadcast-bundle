<?php

namespace Martin1982\LiveBroadcastBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ThumbnailUploadService
 * @package Martin1982\LiveBroadcastBundle\Service
 */
class ThumbnailUploadService
{
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * ThumbnailUploader constructor
     *
     * @param string $targetDirectory
     */
    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @param UploadedFile $file
     *
     * @return string
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid('', true)).'.'.$file->guessExtension();
        $file->move($this->getTargetDirectory(), $fileName);

        return $fileName;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
