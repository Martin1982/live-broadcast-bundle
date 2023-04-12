<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client;

use DateTimeInterface;
use Google\Http\MediaFileUpload;
use Google\Service\YouTube;
use Google\Service\YouTube\LiveStream;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Entity\Metadata\StreamEvent;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastApiException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastException;
use Martin1982\LiveBroadcastBundle\Exception\LiveBroadcastOutputException;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\Client\Config\YouTubeConfig;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class YouTubeClient
 */
class YouTubeClient
{
    /**
     * @var YouTube|null
     */
    protected ?YouTube $youTubeClient = null;

    /**
     * YouTubeClient constructor
     *
     * @param YouTubeConfig $config
     * @param GoogleClient  $googleClient
     */
    public function __construct(protected YouTubeConfig $config, protected GoogleClient $googleClient)
    {
    }

    /**
     * @param YouTube $service
     */
    public function setYouTubeClient(YouTube $service): void
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
        $tokenResult = $client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (array_key_exists('error', $tokenResult)) {
            $error = sprintf('Cannot connect YouTube channel %s: %s', $channel->getChannelName(), $tokenResult['error']);
            throw new LiveBroadcastOutputException($error);
        }

        $this->setYouTubeClient(new YouTube($client));
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     *
     * @return YouTube\LiveBroadcast
     *
     * @throws LiveBroadcastApiException
     * @throws \Exception
     */
    public function createBroadcast(LiveBroadcast $plannedBroadcast): YouTube\LiveBroadcast
    {
        $broadcastSnippet = $this->createBroadcastSnippet($plannedBroadcast);

        $monitorStreamData = new YouTube\MonitorStreamInfo();
        $monitorStreamData->setEnableMonitorStream(false);

        $contentDetails = new YouTube\LiveBroadcastContentDetails();
        $contentDetails->setMonitorStream($monitorStreamData);
        $contentDetails->setEnableAutoStart(true);

        $status = new YouTube\LiveBroadcastStatus();
        $status->setPrivacyStatus($this->convertPrivacyStatus($plannedBroadcast->getPrivacyStatus()));
        $status->setSelfDeclaredMadeForKids(false);

        $liveBroadcast = new YouTube\LiveBroadcast();
        $liveBroadcast->setContentDetails($contentDetails);
        $liveBroadcast->setSnippet($broadcastSnippet);
        $liveBroadcast->setStatus($status);
        $liveBroadcast->setKind('youtube#liveBroadcast');

        try {
            return $this->youTubeClient->liveBroadcasts->insert('snippet,contentDetails,status', $liveBroadcast);
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * @param YouTube\LiveBroadcast $youtubeBroadcast
     * @param LiveBroadcast         $plannedBroadcast
     *
     * @return bool
     */
    public function addThumbnailToBroadcast(YouTube\LiveBroadcast $youtubeBroadcast, LiveBroadcast $plannedBroadcast): bool
    {
        $plannedThumbnail = $plannedBroadcast->getThumbnail();

        if (!$plannedThumbnail instanceof File || !$plannedThumbnail->isFile()) {
            return false;
        }

        $chunkSizeBytes = (1024 * 1024);
        $client = $this->youTubeClient->getClient();

        if (!$client) {
            return false;
        }

        $client->setDefer(true);
        $thumbnailPath = $plannedThumbnail->getRealPath();

        /** @var \Psr\Http\Message\RequestInterface $setRequest */
        $setRequest = $this->youTubeClient->thumbnails->set($youtubeBroadcast->getId());
        $fileUpload = new MediaFileUpload(
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
     * @param int|string $externalId
     *
     * @throws LiveBroadcastApiException
     */
    public function endLiveStream(int|string $externalId): void
    {
        try {
            $this->youTubeClient->liveBroadcasts->transition('complete', $externalId, 'status');
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * Remove a planned live event on YouTube
     *
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastApiException
     */
    public function removeLiveStream(StreamEvent $event): void
    {
        try {
            $this->youTubeClient->liveBroadcasts->delete($event->getExternalStreamId());
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * @param StreamEvent $event
     *
     * @throws LiveBroadcastApiException
     */
    public function updateLiveStream(StreamEvent $event): void
    {
        $plannedBroadcast = $event->getBroadcast();
        $externalId = $event->getExternalStreamId();
        if (!$plannedBroadcast || !$externalId) {
            return;
        }

        $broadcastSnippet = $this->createBroadcastSnippet($plannedBroadcast);

        $liveBroadcast = new YouTube\LiveBroadcast();
        $liveBroadcast->setId($externalId);
        $liveBroadcast->setSnippet($broadcastSnippet);
        $liveBroadcast->setKind('youtube#liveBroadcast');
        $this->addThumbnailToBroadcast($liveBroadcast, $event->getBroadcast());

        try {
            $this->youTubeClient->liveBroadcasts->update('snippet', $liveBroadcast);
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * @param string $title
     *
     * @return LiveStream
     *
     * @throws LiveBroadcastApiException
     */
    public function createStream(string $title): YouTube\LiveStream
    {
        $streamSnippet = new YouTube\LiveStreamSnippet();
        $streamSnippet->setTitle($title);

        $cdn = new YouTube\CdnSettings();
        $cdn->setResolution('variable');
        $cdn->setFrameRate('variable');
        $cdn->setIngestionType('rtmp');

        $streamInsert = new YouTube\LiveStream();
        $streamInsert->setSnippet($streamSnippet);
        $streamInsert->setCdn($cdn);
        $streamInsert->setKind('youtube#liveStream');

        try {
            return $this->youTubeClient->liveStreams->insert('snippet,cdn', $streamInsert);
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * @param YouTube\LiveBroadcast $broadcast
     * @param LiveStream            $stream
     *
     * @return YouTube\LiveBroadcast
     *
     * @throws LiveBroadcastApiException
     */
    public function bind(YouTube\LiveBroadcast $broadcast, YouTube\LiveStream $stream): YouTube\LiveBroadcast
    {
        $broadcastId = $broadcast->getId();
        $parameters = 'id,contentDetails';
        $options = ['streamId' => $stream->getId()];

        try {
            return $this->youTubeClient->liveBroadcasts->bind($broadcastId, $parameters, $options);
        } catch (\Throwable $exception) {
            throw new LiveBroadcastApiException($exception->getMessage());
        }
    }

    /**
     * @param string $youTubeId
     *
     * @return YouTube\LiveBroadcast|null
     *
     * @throws LiveBroadcastException
     */
    public function getYoutubeBroadcast(string $youTubeId): ?YouTube\LiveBroadcast
    {
        $broadcasts = $this->youTubeClient
            ->liveBroadcasts
            ->listLiveBroadcasts('status,contentDetails', [ 'id' => $youTubeId])
            ->getItems();

        if (!count($broadcasts)) {
            throw new LiveBroadcastException(sprintf('No broadcast found for YouTube ID: %s', $youTubeId));
        }

        return $broadcasts[0];
    }

    /**
     * @param string $streamId
     *
     * @return string|null
     */
    public function getStreamUrl(string $streamId): ?string
    {
        /** @var YouTube\LiveStream $stream */
        $stream = $this->youTubeClient
            ->liveStreams
            ->listLiveStreams('snippet,cdn,status', [ 'id' => $streamId])
            ->current();

        $ingestion = $stream->getCdn()->getIngestionInfo();

        $address = $ingestion->getIngestionAddress();
        $name = $ingestion->getStreamName();

        return $address.'/'.$name;
    }

    /**
     * Get a list of live streams
     *
     * @return array
     */
    public function getStreamsList(): array
    {
        $response = $this->youTubeClient
            ->liveBroadcasts
            ->listLiveBroadcasts('snippet,contentDetails,status', ['broadcastStatus' => 'all']);

        return $response->getItems();
    }

    /**
     * FunctionDescription
     *
     * @param LiveBroadcast $plannedBroadcast
     *
     * @return YouTube\LiveBroadcastSnippet
     */
    protected function createBroadcastSnippet(LiveBroadcast $plannedBroadcast): YouTube\LiveBroadcastSnippet
    {
        $start = $plannedBroadcast->getStartTimestamp();

        if (new \DateTime() > $start) {
            $start = new \DateTime('+1 second');
        }

        $broadcastSnippet = new YouTube\LiveBroadcastSnippet();
        $broadcastSnippet->setTitle($plannedBroadcast->getName());
        $broadcastSnippet->setDescription($plannedBroadcast->getDescription());
        $broadcastSnippet->setScheduledStartTime($start->format(DateTimeInterface::ATOM));
        $broadcastSnippet->setScheduledEndTime($plannedBroadcast->getEndTimestamp()->format(DateTimeInterface::ATOM));

        return $broadcastSnippet;
    }

    /**
     * @param int $privacyStatus
     *
     * @return string
     */
    private function convertPrivacyStatus(int $privacyStatus): string
    {
        return match ($privacyStatus) {
            LiveBroadcast::PRIVACY_STATUS_UNLISTED => 'unlisted',
            LiveBroadcast::PRIVACY_STATUS_PRIVATE => 'private',
            default => 'public',
        };
    }
}
