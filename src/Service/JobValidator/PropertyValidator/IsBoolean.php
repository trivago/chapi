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
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class IsBoolean extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'BooleanValidator';
    const MESSAGE_TEMPLATE = '"%s" is not boolean';

    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntity $oJobEntity)
    {
        return $this->returnIsValidHelper(
            is_bool($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }
}