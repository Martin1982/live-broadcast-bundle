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
            ->with('General', array(
                    'class' => 'col-md-8'
                ))
                ->add('name', 'text', array('label' => 'Name'))
                ->add('start_timestamp', 'datetime', array('label' => 'Broadcast start'))
                ->add('end_timestamp', 'datetime', array('label' => 'Broadcast end'))
            ->end()
            ->with('Video Input', array(
                    'class' => 'col-md-4'
                ))
                ->add('input', 'sonata_type_model_list', array())
            ->end()
            ->with('Channels', array(
                    'class' => 'col-md-4'
                ))
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
