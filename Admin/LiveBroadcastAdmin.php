<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\Channel\PlanableChannelInterface;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\Service\ChannelApi\ChannelApiStack;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * @var ChannelApiStack|null
     */
    protected $apiStack;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $code, string $class, string $baseControllerName)
    {
        $this->baseRoutePattern = 'broadcast';
        $this->datagridValues = [
            '_page' => 1,
            '_sort_order' => 'DESC',
            '_sort_by' => 'startTimestamp',
        ];

        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * @param ChannelApiStack $stack
     */
    public function setApiStack(ChannelApiStack $stack): void
    {
        $this->apiStack = $stack;
    }

    /**
     * @param LiveBroadcast $broadcast
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     */
    public function postPersist($broadcast)
    {
        $this->loadThumbnail($broadcast);

        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->createLiveEvent($broadcast, $channel);
                }
            }
        }

        parent::postPersist($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function postUpdate($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->updateLiveEvent($broadcast, $channel);
                }
            }
        }

        parent::postUpdate($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function preRemove($broadcast)
    {
        foreach ($broadcast->getOutputChannels() as $channel) {
            if ($channel instanceof PlanableChannelInterface) {
                $api = $this->apiStack->getApiForChannel($channel);

                if ($api) {
                    $api->removeLiveEvent($broadcast, $channel);
                }
            }
        }

        parent::preRemove($broadcast);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
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
            ->add('input', ModelListType::class)
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
     * @param LiveBroadcast $liveBroadcast
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     * @throws \InvalidArgumentException
     */
    protected function loadThumbnail(LiveBroadcast $liveBroadcast): void
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
     *
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
     * @return null|ContainerInterface
     */
    private function getContainer(): ?ContainerInterface
    {
        return $this->getConfigurationPool()->getContainer();
    }
}
