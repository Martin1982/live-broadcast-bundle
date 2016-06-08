<?php

namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Input\InputFile;
use Martin1982\LiveBroadcastBundle\Entity\Input\InputUrl;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class InputAdmin
 * @package Martin1982\LiveBroadcastBundle\Admin
 */
class InputAdmin extends Admin
{
    protected $baseRoutePattern = 'broadcast-input';

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->tab('General')
            ->with('General');

        if ($subject instanceof InputFile) {
            $formMapper->add('fileLocation', 'text', array('label' => 'File location on server'));
        }

        if ($subject instanceof InputUrl) {
            $formMapper->add('url', 'text', array('label' => 'URL to videofile'));
        }

        $formMapper->end()
            ->end();
    }
}
