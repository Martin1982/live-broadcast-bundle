<?php
declare(strict_types=1);

/**
 * This file is part of martin1982/livebroadcastbundle which is released under MIT.
 * See https://opensource.org/licenses/MIT for full license details.
 */
namespace Martin1982\LiveBroadcastBundle\Admin;

use Martin1982\LiveBroadcastBundle\Entity\Media\MediaFile;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaRtmp;
use Martin1982\LiveBroadcastBundle\Entity\Media\MediaUrl;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class InputAdmin
 */
class InputAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'broadcast-input';

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();

        $formMapper
            ->tab('General')
            ->with('General');

        if ($subject instanceof MediaFile) {
            $formMapper->add('fileLocation', TextType::class, ['label' => 'File location on server']);
        }

        if ($subject instanceof MediaRtmp) {
            $formMapper->add('rtmpAddress', TextType::class, ['label' => 'Address of the RTMP stream to repeat']);
        }

        if ($subject instanceof MediaUrl) {
            $formMapper->add('url', TextType::class, ['label' => 'URL to videofile']);
        }

        $formMapper->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->add('__toString', 'string', ['label' => 'Input']);
    }
}
