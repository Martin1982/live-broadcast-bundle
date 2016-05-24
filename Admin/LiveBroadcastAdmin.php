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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('General')
                ->with('General')
                    ->add('name', 'text', array('label' => 'Name'))
                    ->add('video_input_file', 'text', array('label' => 'Video input file'))
                    ->add('start_timestamp', 'datetime', array('label' => 'Broadcast start'))
                    ->add('end_timestamp', 'datetime', array('label' => 'Broadcast end'))
                    ->add('live_on_youtube', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes'), 'label' => 'Youtube'))
                    ->add('live_on_twitch', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes'), 'label' => 'Twitch'))
                    ->add('live_on_facebook', 'choice', array('choices' => array(0 => 'No', 1 => 'Yes'), 'label' => 'Facebook'))
                ->end()
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('startTimestamp')
            ->add('endTimestamp');
    }

    /**
     * @param ListMapper $listMapper
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