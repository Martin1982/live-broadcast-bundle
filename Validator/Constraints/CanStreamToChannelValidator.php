<?php declare(strict_types=1);

/**
 * live-broadcast-bundle - All rights reserved
 */
namespace Martin1982\LiveBroadcastBundle\Validator\Constraints;

use Martin1982\LiveBroadcastBundle\Entity\Channel\AbstractChannel;
use Martin1982\LiveBroadcastBundle\Service\StreamOutputService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class CanStreamToChannelValidator
 */
class CanStreamToChannelValidator extends ConstraintValidator
{
    /**
     * @var StreamOutputService
     */
    public $outputService;

    /**
     * CanStreamToChannelValidator constructor.
     *
     * @param StreamOutputService $outputService
     */
    public function __construct(StreamOutputService $outputService)
    {
        $this->outputService = $outputService;
    }

    /**
     * Validate streaming to a channel
     *
     * @param AbstractChannel $channel
     * @param Constraint      $constraint
     */
    public function validate($channel, Constraint $constraint): void
    {
        if (!$constraint instanceof CanStreamToChannel) {
            throw new UnexpectedTypeException($constraint, CanStreamToChannel::class);
        }

        if (null === $channel || '' === $channel) {
            return;
        }

        if (!$channel instanceof AbstractChannel) {
            throw new UnexpectedValueException($channel, AbstractChannel::class);
        }

        // validate abstract channel
        try {
            $this->outputService->testOutput($channel);
        } catch (\Exception $exception) {
            $this->context->buildViolation($constraint->message)
                ->atPath('channelName')
                ->setParameter('{{ reason }}', $exception->getMessage())
                ->addViolation();

            return;
        }

        $channel->setIsHealthy(true);
    }
}
