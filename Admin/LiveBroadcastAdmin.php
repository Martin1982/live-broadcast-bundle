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
                    ->add('outputChannels', 'sonata_type_collection', array(), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'channelName',
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
            ->add('live_on_youtube', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes')))
            ->add('live_on_twitch', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes')))
            ->add('live_on_facebook', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes')))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
        ;
    }
}
