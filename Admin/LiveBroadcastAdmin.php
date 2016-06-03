<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class LiveBroadcastAdmin
 * @package Martin1982\LiveBroadcastBundle\Admin
 */
class LiveBroadcastAdmin extends Admin
{
    protected $baseRoutePattern = 'broadcast';

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('General')
                ->with('General')
                    ->add('name', 'text', array('label' => 'Name'))
                    ->add('start_timestamp', 'datetime', array('label' => 'Broadcast start'))
                    ->add('end_timestamp', 'datetime', array('label' => 'Broadcast end'))
                ->end()
            ->end()
            ->tab('Video Input')
                ->with('Video Input')
                    ->add('video_input_file', 'text', array('label' => 'Video input file'))
                ->end()
            ->end()
            ->tab('Channels')
                ->with('Channels')
                    ->add('outputChannels', 'sonata_type_model', array(
                        'multiple' => true,
                        'btn_add' => false,
                    ))
                ->end();
    }

    /**
     * {@inheritdoc}
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
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('startTimestamp')
            ->add('endTimestamp')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }
}
