<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYoutube;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class ChannelAdmin.
 */
class ChannelAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'channel';

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        $subject = $this->getSubject();

        if ($subject instanceof ChannelFacebook && $name === 'edit') {
            return 'LiveBroadcastBundle:CRUD:channel_facebook_edit.html.twig';
        }

        if ($subject instanceof ChannelYoutube && $name ==='edit') {
            return 'LiveBroadcastBundle:CRUD:channel_youtube_edit.html.twig';
        }

        return parent::getTemplate($name);
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
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $nameClasses = 'generic-channel-name';
        if ($subject instanceof ChannelYoutube) {
            $nameClasses = 'generic-channel-name input-yt-channelname';
        }

        $formMapper
            ->with('Channel')
                ->add('channelName', 'text', array(
                    'label' => 'Channel name',
                    'attr' => array('class' => $nameClasses),
                ));

        if ($subject instanceof ChannelTwitch) {
            $formMapper->add('streamKey', 'text', array('label' => 'Twitch stream key'));
            $formMapper->add('streamServer', 'text', array('label' => 'Twitch stream server'));
        }

        if ($subject instanceof ChannelFacebook) {
            $formMapper->add('accessToken', 'hidden', array(
                'attr' => array('class' => 'fb-access-token'),
            ));
            $formMapper->add('fbEntityId', 'hidden', array(
                'attr' => array('class' => 'fb-entity-id'),
            ));
        }

        if ($subject instanceof ChannelYoutube) {
            $formMapper->add('youtubeChannelName', 'text', array(
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
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('channelName');
    }

    /**
     * {@inheritdoc}
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
