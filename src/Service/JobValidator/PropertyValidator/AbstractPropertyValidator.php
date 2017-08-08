<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 */


namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

abstract class AbstractPropertyValidator implements PropertyValidatorInterface
{
    /** @var string  */
    protected $lastErrorMessage = '';

    /**
     * @inheritDoc
     */
    abstract public function isValid($property, JobEntityInterface $jobEntity);
    
    /**
     * @inheritDoc
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @param boolean $isValid
     * @param string $property
     * @param string $errorMessageTemplate
     * @return bool
     */
    protected function returnIsValidHelper($isValid, $property, $errorMessageTemplate)
    {
        if (!$isValid) {
            $this->lastErrorMessage = sprintf($errorMessageTemplate, $property);
            return false;
        }

        $this->lastErrorMessage = '';
        return true;
    }
}
