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

class JobName extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'JobNameValidator';
    
    const REG_EX_VALID_NAME = '/^[a-zA-Z0-9_-]*$/';
    const MESSAGE_TEMPLATE = '"%s" contains invalid characters. Please use only "a-z", "A-Z", "0-9" "-" and "_"';

    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntity $oJobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isNamePropertyValid($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param string $sName
     * @return bool
     */
    private function isNamePropertyValid($sName)
    {
        return (!empty($sName) && preg_match(self::REG_EX_VALID_NAME, $sName));
    }
}