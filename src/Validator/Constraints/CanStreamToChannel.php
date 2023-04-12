<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved.
 */
namespace Martin1982\LiveBroadcastBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CanStreamToChannel.
 *
 * @Annotation
 */
class CanStreamToChannel extends Constraint
{
    /**
     * @var string
     */
    public string $message = 'Unable to stream because the channel {{ reason }}';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
