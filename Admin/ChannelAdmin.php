<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Channel\BaseChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelLively;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class ChannelAdmin
 * @package Martin1982\LiveBroadcastBundle\Admin
 */
class ChannelAdmin extends AbstractAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'channel';

    /**
     * @var array
     */
    protected $subclassConfigs = array();

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        $subject = $this->getSubject();

        if ($subject instanceof ChannelFacebook && $name === 'edit') {
            return 'LiveBroadcastBundle:CRUD:channel_facebook_edit.html.twig';
        }

        if ($subject instanceof ChannelYouTube && $name ==='edit') {
            return 'LiveBroadcastBundle:CRUD:channel_youtube_edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    /**
     * Set configuration for the subclass configs
     *
     * @param $configs
     */
    public function setSubclassConfigs($configs)
    {
        $this->subclassConfigs = $configs;
    }

    /**
     * @param BaseChannel[] $subclasses
     */
    public function setConfiguredSubclasses($subclasses)
    {
        $configuredSubclasses = array();
        $config = $this->subclassConfigs;

        foreach ($subclasses as $classKey => $subclass) {
            if ($subclass::isEntityConfigured($config)) {
                $configuredSubclasses[$classKey] = $subclass;
            }
        }

        $this->setSubClasses($configuredSubclasses);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('longLivedAccessToken', 'facebook/accesstoken');
        $collection->add('youtubeoauth', 'youtube/oauthprovider');
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $nameClasses = 'generic-channel-name';
        if ($subject instanceof ChannelYouTube) {
            $nameClasses = 'generic-channel-name input-yt-channelname';
        }

        $formMapper
            ->with('Channel')
                ->add('channelName', 'text', array(
                    'label' => 'Channel name',
                    'attr' => array('class' => $nameClasses),
                ));

        if ($subject instanceof ChannelTwitch ||
            $subject instanceof ChannelUstream ||
            $subject instanceof ChannelLively) {
            $formMapper->add('streamKey', 'text', array('label' => 'Stream key'));
            $formMapper->add('streamServer', 'text', array('label' => 'Stream server'));
        }

        if ($subject instanceof ChannelFacebook) {
            $formMapper->add('accessToken', 'hidden', array(
                'attr' => array('class' => 'fb-access-token'),
            ));
            $formMapper->add('fbEntityId', 'hidden', array(
                'attr' => array('class' => 'fb-entity-id'),
            ));
        }

        if ($subject instanceof ChannelYouTube) {
            $formMapper->add('youTubeChannelName', 'text', array(
                'attr' => array('class' => 'input-yt-channelname', 'readonly' => 'readonly')
            ));

            $formMapper->add('refreshToken', 'text', array(
                'attr' => array('class' => 'input-yt-refreshtoken', 'readonly' => 'readonly')
            ));
        }

        $formMapper->end();
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('channelName');
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('channelName')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
            ));
    }
}
