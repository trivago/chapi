<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 * @link:    http://
 */


namespace Chapi\Service\JobValidator\PropertyValidator;


use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class IsArray extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'IsArrayValidator';
    const MESSAGE_TEMPLATE = '"%s" is not an array';

    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            is_array($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }
}