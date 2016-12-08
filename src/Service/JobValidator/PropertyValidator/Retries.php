<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 */


namespace Chapi\Service\JobValidator\PropertyValidator;


use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Retries extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'RetriesValidator';
    const MESSAGE_TEMPLATE = '"%s" is not numeric or greater than or equal 0';

    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            (is_numeric($oJobEntity->{$sProperty}) && $oJobEntity->{$sProperty} >= 0),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }
}