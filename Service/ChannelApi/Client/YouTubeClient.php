<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\YouTubeConfig;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeClient
 */
class YouTubeClient
{
    /**
     * @var YouTubeConfig
     */
    protected $config;

    /**
     * @var GoogleClient
     */
    protected $googleClient;

    /**
     * @var \Google_Service_YouTube|null
     */
    protected $youTubeClient;

    /**
     * YouTubeClient constructor
     *
     * @param YouTubeConfig $config
     * @param GoogleClient  $googleClient
     */
    public function __construct(YouTubeConfig $config, GoogleClient $googleClient)
    {
        $this->config = $config;
        $this->googleClient = $googleClient;
    }

    /**
     * @param \Google_Service_YouTube $service
     */
    public function setYouTubeClient(\Google_Service_YouTube $service): void
    {
        $this->youTubeClient = $service;
    }

    /**
     * @param ChannelYouTube $channel
     *
     * @throws LiveBroadcastOutputException
     */
    public function setChannel(ChannelYouTube $channel): void
    {
        $refreshToken = $channel->getRefreshToken();
        $client = $this->googleClient->getClient();
        if (!$client) {
            throw new LiveBroadcastOutputException('No Google client available');
        }
        $client->fetchAccessTokenWithRefreshToken($refreshToken);

        $this->setYouTubeClient(new \Google_Service_YouTube($client));
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws LiveBroadcastOutputException
     */
    public function createBroadcast(LiveBroadcast $plannedBroadcast): \Google_Service_YouTube_LiveBroadcast
    {
        $start = $plannedBroadcast->getStartTimestamp();

        if (new \DateTime() > $start) {
            $start = new \DateTime('+1 second');
        }

        $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($plannedBroadcast->getName());
        $broadcastSnippet->setDescription($plannedBroadcast->getDescription());
        $broadcastSnippet->setScheduledStartTime($start->format(\DateTime::ATOM));
        $broadcastSnippet->setScheduledEndTime($plannedBroadcast->getEndTimestamp()->format(\DateTime::ATOM));

        $monitorStreamData = new \Google_Service_YouTube_MonitorStreamInfo();
        $monitorStreamData->setEnableMonitorStream(false);

        $contentDetails = new \Google_Service_YouTube_LiveBroadcastContentDetails();
        $contentDetails->setMonitorStream($monitorStreamData);
        $contentDetails->setEnableAutoStart(true);

        $status = new \Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus('public');

        $liveBroadcast = new \Google_Service_YouTube_LiveBroadcast();
        $liveBroadcast->setContentDetails($contentDetails);
        $liveBroadcast->setSnippet($broadcastSnippet);
        $liveBroadcast->setStatus($status);
        $liveBroadcast->setKind('youtube#liveBroadcast');

        try {
            return $this->youTubeClient->liveBroadcasts->insert('snippet,contentDetails,status', $liveBroadcast);
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * @param \Google_Service_YouTube_LiveBroadcast $youtubeBroadcast
     * @param LiveBroadcast                         $plannedBroadcast
     *
     * @return bool
     *
     * @throws LiveBroadcastOutputException
     */
    public function addThumbnailToBroadcast(\Google_Service_YouTube_LiveBroadcast $youtubeBroadcast, LiveBroadcast $plannedBroadcast): bool
    {
        $plannedThumbnail = $plannedBroadcast->getThumbnail();

        if (!$plannedThumbnail instanceof File || !$plannedThumbnail->isFile()) {
            return false;
        }

        $chunkSizeBytes = (1 * 1024 * 1024);
        $client = $this->googleClient->getClient();

        if (!$client) {
            return false;
        }

        $client->setDefer(true);
        $thumbnailPath = $plannedThumbnail->getRealPath();

        /** @var \Psr\Http\Message\RequestInterface $setRequest */
        $setRequest = $this->youTubeClient->thumbnails->set($youtubeBroadcast->getId());
        $fileUpload = new \Google_Http_MediaFileUpload(
            $client,
            $setRequest,
            mime_content_type($thumbnailPath),
            null,
            true,
            $chunkSizeBytes
        );
        $fileUpload->setFileSize(filesize($thumbnailPath));

        $status = false;
        $handle = fopen($thumbnailPath, 'rb');
        while (!$status && !feof($handle)) {
            $chunk = fread($handle, $chunkSizeBytes);
            $status = $fileUpload->nextChunk($chunk);
        }

        fclose($handle);
        $client->setDefer(false);

        return true;
    }

    /**
     * @param string|int $externalId
     *
     * @throws LiveBroadcastOutputException
     */
    public function endLiveStream($externalId): void
    {
        try {
            $this->youTubeClient->liveBroadcasts->transition('complete', $externalId, 'status');
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * Remove a planned live event on YouTube
     *
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastOutputException
     */
    public function removeLivestream(StreamEvent $event): void
    {
        try {
            $this->youTubeClient->liveBroadcasts->delete($event->getExternalStreamId());
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastOutputException
     */
    public function updateLiveStream(StreamEvent $event): void
    {
        $plannedBroadcast = $event->getBroadcast();
        $externalId = $event->getExternalStreamId();
        if (!$plannedBroadcast || !$externalId) {
            return;
        }
        $start = $plannedBroadcast->getStartTimestamp();

        if (new \DateTime() > $start) {
            $start = new \DateTime('+1 second');
        }

        $broadcastSnippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($plannedBroadcast->getName());
        $broadcastSnippet->setDescription($plannedBroadcast->getDescription());
        $broadcastSnippet->setScheduledStartTime($start->format(\DateTime::ATOM));
        $broadcastSnippet->setScheduledEndTime($plannedBroadcast->getEndTimestamp()->format(\DateTime::ATOM));

        $liveBroadcast = new \Google_Service_YouTube_LiveBroadcast();
        $liveBroadcast->setId($externalId);
        $liveBroadcast->setSnippet($broadcastSnippet);
        $liveBroadcast->setKind('youtube#liveBroadcast');

        try {
            $this->youTubeClient->liveBroadcasts->update('snippet', $liveBroadcast);
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * @param string $title
     *
     * @return \Google_Service_YouTube_LiveStream
     *
     * @throws LiveBroadcastOutputException
     */
    public function createStream(string $title): \Google_Service_YouTube_LiveStream
    {
        $streamSnippet = new \Google_Service_YouTube_LiveStreamSnippet();
        $streamSnippet->setTitle($title);

        $cdn = new \Google_Service_YouTube_CdnSettings();
        $cdn->setFormat('720p');
        $cdn->setIngestionType('rtmp');

        $streamInsert = new \Google_Service_YouTube_LiveStream();
        $streamInsert->setSnippet($streamSnippet);
        $streamInsert->setCdn($cdn);
        $streamInsert->setKind('youtube#liveStream');

        try {
            return $this->youTubeClient->liveStreams->insert('snippet,cdn', $streamInsert);
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * @param \Google_Service_YouTube_LiveBroadcast $broadcast
     * @param \Google_Service_YouTube_LiveStream    $stream
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     *
     * @throws LiveBroadcastOutputException
     */
    public function bind(\Google_Service_YouTube_LiveBroadcast $broadcast, \Google_Service_YouTube_LiveStream $stream): \Google_Service_YouTube_LiveBroadcast
    {
        $broadcastId = $broadcast->getId();
        $parameters = 'id,contentDetails';
        $options = ['streamId' => $stream->getId()];

        try {
            return $this->youTubeClient->liveBroadcasts->bind($broadcastId, $parameters, $options);
        } catch (\Google_Service_Exception $exception) {
            throw new LiveBroadcastOutputException($exception->getMessage());
        }
    }

    /**
     * @param string $youTubeId
     *
     * @return \Google_Service_YouTube_LiveBroadcast|null
     */
    public function getYoutubeBroadcast(string $youTubeId): ?\Google_Service_YouTube_LiveBroadcast
    {
        /** @var \Google_Service_YouTube_LiveBroadcast|null $broadcast */
        $broadcast = $this->youTubeClient
            ->liveBroadcasts
            ->listLiveBroadcasts('status,contentDetails', [ 'id' => $youTubeId])
            ->current();

        return $broadcast;
    }

    /**
     * @param string $streamId
     *
     * @return string|null
     */
    public function getStreamUrl($streamId): ?string
    {
        /** @var \Google_Service_YouTube_LiveStream $stream */
        $stream = $this->youTubeClient
            ->liveStreams
            ->listLiveStreams('snippet,cdn,status', [ 'id' => $streamId])
            ->current();

        $ingestion = $stream->getCdn()->getIngestionInfo();

        $address = $ingestion->getIngestionAddress();
        $name = $ingestion->getStreamName();

        return $address.'/'.$name;
    }
}
