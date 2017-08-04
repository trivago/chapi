<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */

namespace Chapi\Entity\JobValidator;

class ValidationResult
{
    /**
     * @var string
     */
    public $propertyName = '';

    /**
     * @var bool
     */
    public $isValid = false;

    /**
     * @var string
     */
    public $errorMessage = '';

    /**
     * ValidationResult constructor.
     * @param string $propertyName
     * @param boolean $isValid
     * @param string $errorMessage
     */
    public function __construct($propertyName, $isValid, $errorMessage)
    {
        $this->propertyName = $propertyName;
        $this->isValid = $isValid;
        if (!$isValid) {
            $this->errorMessage = $errorMessage;
        }
    }
}
