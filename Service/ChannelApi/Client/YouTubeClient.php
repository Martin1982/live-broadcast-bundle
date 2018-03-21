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

        $this->youTubeClient = new \Google_Service_YouTube($client);
    }

    /**
     * @param LiveBroadcast $plannedBroadcast
     *
     * @return \Google_Service_YouTube_LiveBroadcast
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

        $plannedThumbnail = $plannedBroadcast->getThumbnail();
        if ($plannedThumbnail instanceof File && $plannedThumbnail->isFile()) {
            $thumbnails = $this->getThumbnails($plannedThumbnail);
            $broadcastSnippet->setThumbnails($thumbnails);
        }

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

        return $this->youTubeClient->liveBroadcasts->insert('snippet,contentDetails,status', $liveBroadcast);
    }

    /**
     * Remove a planned live event on YouTube
     *
     * @param StreamEvent $event
     */
    public function removeLivestream(StreamEvent $event): void
    {
        $this->youTubeClient->liveBroadcasts->delete($event->getExternalStreamId());
    }

    /**
     * @param StreamEvent $event
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

        $plannedThumbnail = $plannedBroadcast->getThumbnail();
        if ($plannedThumbnail instanceof File && $plannedThumbnail->isFile()) {
            $thumbnails = $this->getThumbnails($plannedThumbnail);
            $broadcastSnippet->setThumbnails($thumbnails);
        }

        $liveBroadcast = new \Google_Service_YouTube_LiveBroadcast();
        $liveBroadcast->setId($externalId);
        $liveBroadcast->setSnippet($broadcastSnippet);
        $liveBroadcast->setKind('youtube#liveBroadcast');

        $this->youTubeClient->liveBroadcasts->update('snippet', $liveBroadcast);
    }

    /**
     * @param string $title
     *
     * @return \Google_Service_YouTube_LiveStream
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

        return $this->youTubeClient->liveStreams->insert('snippet,cdn', $streamInsert);
    }

    /**
     * @param \Google_Service_YouTube_LiveBroadcast $broadcast
     * @param \Google_Service_YouTube_LiveStream    $stream
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    public function bind(\Google_Service_YouTube_LiveBroadcast $broadcast, \Google_Service_YouTube_LiveStream $stream): \Google_Service_YouTube_LiveBroadcast
    {
        $broadcastId = $broadcast->getId();
        $parameters = 'id,contentDetails';
        $options = ['streamId' => $stream->getId()];

        return $this->youTubeClient->liveBroadcasts->bind($broadcastId, $parameters, $options);
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

        $address = $stream->getCdn()->getIngestionInfo()->getIngestionAddress();
        $name = $stream->getCdn()->getIngestionInfo()->getStreamName();

        return $address.'/'.$name;
    }

    /**
     * @param File $thumbnail
     *
     * @return \Google_Service_YouTube_ThumbnailDetails
     */
    protected function getThumbnails(File $thumbnail): \Google_Service_YouTube_ThumbnailDetails
    {
        $defaultThumbnail = new \Google_Service_YouTube_Thumbnail();
        $thumbnailUrl = sprintf(
            '%s%s/%s',
            $this->config->getHost(),
            $this->config->getThumbnailDirectory(),
            $thumbnail->getFilename()
        );
        $defaultThumbnail->setUrl($thumbnailUrl);

        [$width, $height] = getimagesize($thumbnail->getRealPath());
        $defaultThumbnail->setWidth($width);
        $defaultThumbnail->setHeight($height);

        $thumbnails = new \Google_Service_YouTube_ThumbnailDetails();
        $thumbnails->setDefault($defaultThumbnail);

        return $thumbnails;
    }
}
