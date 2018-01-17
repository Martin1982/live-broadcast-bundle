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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
    protected $subclassConfigs = [];

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
        $configuredSubclasses = [];
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
                ->add('channelName', TextType::class, [
                    'label' => 'Channel name',
                    'attr' => ['class' => $nameClasses],
                ]);

        if ($subject instanceof ChannelTwitch ||
            $subject instanceof ChannelUstream ||
            $subject instanceof ChannelLively) {
            $formMapper->add('streamKey', TextType::class, ['label' => 'Stream key']);
            $formMapper->add('streamServer', TextType::class, ['label' => 'Stream server']);
        }

        if ($subject instanceof ChannelFacebook) {
            $formMapper->add('accessToken', HiddenType::class, [
                'attr' => ['class' => 'fb-access-token'],
            ]);
            $formMapper->add('fbEntityId', HiddenType::class, [
                'attr' => ['class' => 'fb-entity-id'],
            ]);
        }

        if ($subject instanceof ChannelYouTube) {
            $formMapper->add('youTubeChannelName', TextType::class, [
                'attr' => ['class' => 'input-yt-channelname', 'readonly' => 'readonly']
            ]);

            $formMapper->add('refreshToken', TextType::class, [
                'attr' => ['class' => 'input-yt-refreshtoken', 'readonly' => 'readonly']
            ]);
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
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
