<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */


namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Constraints extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'ConstraintsValidator';
    const MESSAGE_TEMPLATE = '"%s" contained constraint is not valid. Should be an array with three values.';

    /**
     * @inheritDoc
     */
    public function isValid($property, JobEntityInterface $jobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isConstraintsPropertyValid($jobEntity->{$property}),
            $property,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param array $constraints
     * @return bool
     */
    private function isConstraintsPropertyValid($constraints)
    {
        if (!is_array($constraints) && !is_null($constraints)) {
            return false;
        }
        
        if (!empty($constraints)) {
            foreach ($constraints as $constraint) {
                if (!is_array($constraint) || count($constraint) != 3) {
                    return false;
                }
            }
        }

        return true;
    }
}
