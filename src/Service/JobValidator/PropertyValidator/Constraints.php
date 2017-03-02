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
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isConstraintsPropertyValid($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param array $aConstraints
     * @return bool
     */
    private function isConstraintsPropertyValid($aConstraints)
    {
        if (!is_array($aConstraints) && !is_null($aConstraints))
        {
            return false;
        }
        
        if (!empty($aConstraints))
        {
            foreach ($aConstraints as $_aConstraint)
            {
                if (!is_array($_aConstraint) || count($_aConstraint) != 3)
                {
                    return false;
                }
            }
        }

        return true;
    }
}