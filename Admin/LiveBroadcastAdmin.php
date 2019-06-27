<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Martin1982\LiveBroadcastBundle\EventListener\ThumbnailUploadListener;
use Martin1982\LiveBroadcastBundle\Service\BroadcastManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
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
     * @var BroadcastManager
     */
    protected $broadcastManager;

    /**
     * @var ThumbnailUploadListener
     */
    protected $thumbnailListener;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
     * @param BroadcastManager $manager
     */
    public function setBroadcastManager(BroadcastManager $manager): void
    {
        $this->broadcastManager = $manager;
    }

    /**
     * @param ThumbnailUploadListener $listener
     */
    public function setThumbnailListener(ThumbnailUploadListener $listener): void
    {
        $this->thumbnailListener = $listener;
    }

    /**
     * @param AbstractManagerRegistry $registry
     *
     * @throws \InvalidArgumentException
     */
    public function setObjectManager(AbstractManagerRegistry $registry):void
    {
        $this->objectManager = $registry->getManager();
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
        $this->broadcastManager->preInsert($broadcast);

        parent::postPersist($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function postUpdate($broadcast)
    {
        $this->broadcastManager->preUpdate($broadcast);
        parent::postUpdate($broadcast);
    }

    /**
     * @param LiveBroadcast $broadcast
     */
    public function preRemove($broadcast)
    {
        $this->broadcastManager->preDelete($broadcast);
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
            ->add('outputChannels', ModelAutocompleteType::class, [
                'multiple' => true,
                'property' => ['channelName'],
                'btn_add' => 'Add new'
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

        $lifeCycleEvent = new LifecycleEventArgs($liveBroadcast, $this->objectManager);
        $this->thumbnailListener->postLoad($lifeCycleEvent);
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
