<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Entity\LiveBroadcast;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\Form\Type\DateTimePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
    protected $thumbnailPath = '';

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
     * @param string $path
     */
    public function setThumbnailPath(string $path = ''): void
    {
        $this->thumbnailPath = $path;
    }

    /**
     * Get a query for healthy channels
     *
     * @return QueryBuilder
     */
    protected function getHealthyChannelsQuery(): QueryBuilder
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getModelManager();

        return  $modelManager->getEntityManager($this->getSubject())
            ->createQueryBuilder()
            ->addSelect('channel')
            ->from(AbstractChannel::class, 'channel')
            ->where('channel.isHealthy = :healthyParam')
            ->setParameter('healthyParam', true);
    }

    /**
     * Get the HTML for showing the thumbnail image
     *
     * @return string|null
     */
    protected function getThumbnailHtml(): ?string
    {
        $html = null;

        /** @var LiveBroadcast $broadcast */
        $broadcast = $this->getSubject();

        if ($broadcast->getThumbnail()) {
            $fullPath = sprintf('/%s/%s', $this->thumbnailPath, $broadcast->getThumbnail()->getFilename());

            $html = '<img src="'.$fullPath.'" style="max-width: 100%;"/>';
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
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
                ->add('thumbnail', FileType::class, [
                    'required' => false,
                    'label' => 'Thumbnail (min. 1280x720px, 16:9 ratio)',
                    'help' => $this->getThumbnailHtml(),
                ])
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
                ->add('privacyStatus', ChoiceType::class, [
                    'choices' => [
                        'Public' => LiveBroadcast::PRIVACY_STATUS_PUBLIC,
                        'Private' => LiveBroadcast::PRIVACY_STATUS_PRIVATE,
                        'Unlisted' => LiveBroadcast::PRIVACY_STATUS_UNLISTED,
                        ],
                    'label' => 'Privacy status (YouTube only)',
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
                    'query' => $this->getHealthyChannelsQuery(),
                ])
            ->end();
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
            ->addIdentifier('name')
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
}
