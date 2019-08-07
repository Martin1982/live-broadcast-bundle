<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelFacebook;
use Martin1982\LiveBroadcastBundle\Entity\Channel\ChannelYouTube;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlannedChannelInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class ChannelAdmin
 */
class ChannelAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $subclassConfigs = [];

    /**
     * ChannelAdmin constructor
     *
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct(string $code, string $class, string $baseControllerName)
    {
        $this->baseRoutePattern = 'channel';
        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name): string
    {
        $subject = $this->getSubject();

        if ($subject instanceof ChannelFacebook && 'edit' === $name) {
            return 'LiveBroadcastBundle:CRUD:channel_facebook_edit.html.twig';
        }

        if ($subject instanceof ChannelYouTube && 'edit' === $name) {
            return 'LiveBroadcastBundle:CRUD:channel_youtube_edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    /**
     * Set configuration for the subclass configs
     *
     * @param array $configs
     */
    public function setSubclassConfigs($configs): void
    {
        $this->subclassConfigs = $configs;
    }

    /**
     * @param AbstractChannel[] $subclasses
     */
    public function setConfiguredSubclasses($subclasses): void
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
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->add('longLivedAccessToken', 'facebook/accesstoken');
        $collection->add('youtubeoauth', 'youtube/oauthprovider');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper): void
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

        if (!$subject instanceof PlannedChannelInterface) {
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
                'attr' => ['class' => 'input-yt-channelname', 'readonly' => 'readonly'],
            ]);

            $formMapper->add('refreshToken', TextType::class, [
                'attr' => ['class' => 'input-yt-refreshtoken', 'readonly' => 'readonly'],
            ]);
        }

        $formMapper->end();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('channelName');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('channelName')
            ->add('isHealthy')
            ->add('_action', 'actions', [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
