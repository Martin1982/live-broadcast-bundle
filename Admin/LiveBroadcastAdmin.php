<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class LiveBroadcastAdmin.
 */
class LiveBroadcastAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'broadcast';

    /**
     * @var array
     */
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'startTimestamp',
    ];

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $fileFieldOptions = ['required' => false, 'label' => 'Thumbnail (min. 1280x720px, 16:9 ratio)'];

        /** @var LiveBroadcast $broadcast */
        $broadcast = $this->getSubject();

        if ($broadcast->getThumbnail()) {
            $container = $this->getContainer();

            if ($container) {
                $fullPath = sprintf(
                    '%s/%s',
                    $container->getParameter('livebroadcast.thumbnail.web_path'),
                    $broadcast->getThumbnail()->getFilename()
                );

                $fileFieldOptions['help'] = '<img src="'.$fullPath.'" style="max-width: 100%;"/>';
            }
        }

        $formMapper
            ->with('General', [
                'class' => 'col-md-8',
            ])
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            ->add('thumbnail', FileType::class, $fileFieldOptions)
            ->add('startTimestamp', DateTimePickerType::class, [
                'label' => 'Broadcast start',
                'dp_side_by_side' => true,
            ])
            ->add('endTimestamp', DateTimePickerType::class, [
                'label' => 'Broadcast end',
                'dp_side_by_side' => true,
            ])
            ->add('stopOnEndTimestamp', CheckboxType::class, [
                'label' => 'Stop on broadcast end timestamp',
                'required' => false,
            ])
            ->end()
            ->with('Video Input', [
                'class' => 'col-md-4',
            ])
            ->add('input', ModelListType::class, ['btn_list' => false])
            ->end()
            ->with('Channels', [
                'class' => 'col-md-4',
            ])
            ->add('outputChannels', ModelType::class, [
                'multiple' => true,
                'expanded' => true,
                'translation_domain' => false,
            ])
            ->end();
    }

    /**
     * @param LiveBroadcast $broadcast
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function postPersist($broadcast)
    {
        $this->loadThumbnail($broadcast);

        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof ChannelYouTube) {
                $youTubeService = $this->getYouTubeService();
                $youTubeService->createLiveEvent($broadcast, $channel);
            }
        }

        parent::postPersist($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function postUpdate($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof ChannelYouTube) {
                $youTubeService = $this->getYouTubeService();
                $youTubeService->updateLiveEvent($broadcast, $channel);
            }
        }

        parent::postUpdate($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function preRemove($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof ChannelYouTube) {
                $youTubeService = $this->getYouTubeService();
                try {
                    $youTubeService->removeLiveEvent($broadcast, $channel);
                } catch (\Google_Service_Exception $ex) {
                    /** @var LoggerInterface $logger */
                    $container = $this->getContainer();
                    if ($container) {
                        $container->get('logger')->warning($ex->getMessage());
                    }
                }
            }
        }

        parent::preRemove($broadcast);
    }

    /**
     * Get the YouTube Live service
     *
     * @return YouTubeApiService
     *
     * @throws \Exception
     */
    protected function getYouTubeService()
    {
        $container = $this->getContainer();

        if (!$container) {
            throw new \Exception('No service container found');
        }

        $youTubeService = $container->get('live.broadcast.youtubeapi.service');
        $redirectService = $container->get('live.broadcast.googleredirect.service');

        $youTubeService->initApiClients($redirectService->getOAuthRedirectUrl());

        return $youTubeService;
    }

    /**
     * @param LiveBroadcast $liveBroadcast
     *
     * @throws \InvalidArgumentException
     */
    protected function loadThumbnail(LiveBroadcast $liveBroadcast)
    {
        $container = $this->getContainer();

        if (!$container) {
            return;
        }

        $uploadListener = $container->get('live.broadcast.thumbnail.listener');
        $objectManager = $container->get('doctrine')->getManager();
        $lifeCycleEvent = new LifecycleEventArgs($liveBroadcast, $objectManager);
        $uploadListener->postLoad($lifeCycleEvent);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('startTimestamp')
            ->add('endTimestamp');
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('outputChannels', 'sonata_type_model', ['label' => 'Channel(s)'])
            ->add('startTimestamp', 'datetime', ['label' => 'Start time'])
            ->add('endTimestamp', 'datetime', ['label' => 'End time'])
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }
}
