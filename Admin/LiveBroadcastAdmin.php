<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\YouTubeApiService;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class LiveBroadcastAdmin.
 */
class LiveBroadcastAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'broadcast';

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'startTimestamp',
    );

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General', array(
                    'class' => 'col-md-8',
                ))
                ->add('name', 'text', array('label' => 'Name'))
                ->add('description', 'textarea', array(
                    'label' => 'Description',
                    'required' => false,
                    'attr' => array('class' => 'form-control', 'rows' => 5),
                ))
                ->add('startTimestamp', 'sonata_type_datetime_picker', array(
                    'label' => 'Broadcast start',
                    'dp_side_by_side' => true,
                ))
                ->add('endTimestamp', 'sonata_type_datetime_picker', array(
                    'label' => 'Broadcast end',
                    'dp_side_by_side' => true,
                ))
                ->add('stopOnEndTimestamp', 'checkbox', array(
                    'label' => 'Stop on broadcast end timestamp',
                    'required' => false,
                ))
            ->end()
            ->with('Video Input', array(
                    'class' => 'col-md-4',
                ))
                ->add('input', 'sonata_type_model_list', array('btn_list' => false))
            ->end()
            ->with('Channels', array(
                    'class' => 'col-md-4',
                ))
                ->add('outputChannels', 'sonata_type_model', array(
                    'multiple' => true,
                    'expanded' => true,
                ))
                ->end();
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function postPersist($broadcast)
    {
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
     */
    public function preUpdate($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof ChannelYouTube) {
                $youTubeService = $this->getYouTubeService();
                $youTubeService->updateLiveEvent($broadcast, $channel);
            }
        }

        parent::preUpdate($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function preRemove($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof ChannelYouTube) {
                $youTubeService = $this->getYouTubeService();
                $youTubeService->removeLiveEvent($broadcast, $channel);
            }
        }

        parent::preRemove($broadcast);
    }

    /**
     * Get the YouTube Live service
     *
     * @return YouTubeApiService
     * @throws \Exception
     */
    protected function getYouTubeService()
    {
        $service = $this->getConfigurationPool()->getContainer()->get('live.broadcast.youtubeapi.service');
        $router = $this->getConfigurationPool()->getContainer()->get('router');

        $redirectUri = $router->generate(
            $this->getConfigurationPool()->getContainer()->getParameter('live_broadcast_yt_redirect_route'),
            array(),
            Router::ABSOLUTE_URL
        );

        $service->initApiClients($redirectUri);

        return $service;
    }

    /**
     * {@inheritdoc}
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
            ->add('outputChannels', 'sonata_type_model', array('label' => 'Channel(s)'))
            ->add('startTimestamp', 'datetime', array('label' => 'Start time'))
            ->add('endTimestamp', 'datetime', array('label' => 'End time'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }
}
