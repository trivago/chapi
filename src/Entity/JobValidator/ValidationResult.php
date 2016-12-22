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
    public $sPropertyName = '';

    /**
     * @var bool
     */
    public $bIsValid = false;

    /**
     * @var string
     */
    public $sErrorMessage = '';

    /**
     * ValidationResult constructor.
     * @param string $sPropertyName
     * @param boolean $bIsValid
     * @param string $sErrorMessage
     */
    public function __construct($sPropertyName, $bIsValid, $sErrorMessage)
    {
        $this->sPropertyName = $sPropertyName;
        $this->bIsValid = $bIsValid;
        if (!$bIsValid)
        {
            $this->sErrorMessage = $sErrorMessage;
        }
    }
}