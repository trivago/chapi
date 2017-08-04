<?php
/**
 * @package: PropertyValidator
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */

namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class NotEmpty extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'NotEmptyValidator';
    const MESSAGE_TEMPLATE = '"%s" is empty';
    
    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            !empty($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }
}
