<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelTwitch;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelUstream;
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
     * @param string $name
     * @return mixed|null|string
     */
    public function getTemplate($name)
    {
        $subject = $this->getSubject();

        if ($subject instanceof ChannelFacebook && $name === 'edit') {
            return 'LiveBroadcastBundle:CRUD:channel_facebook_edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('longLivedAccessToken', 'facebook/accesstoken');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->with('Channel')
                ->add('channelName', 'text', array('label' => 'Channel name', 'attr' => array('class' => 'generic-channel-name')));

        if ($subject instanceof ChannelTwitch || $subject instanceof ChannelUstream) {
            $formMapper->add('streamKey', 'text', array('label' => 'Stream key'));
            $formMapper->add('streamServer', 'text', array('label' => 'Stream server'));
        }

        if ($subject instanceof ChannelFacebook) {
            $formMapper->add('accessToken', 'hidden', array('label' => 'Facebook access token', 'attr' => array('class' => 'fb-access-token')));
            $formMapper->add('fbEntityId', 'hidden', array('label' => 'Facebook entity ID', 'attr' => array('class' => 'fb-entity-id')));
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
