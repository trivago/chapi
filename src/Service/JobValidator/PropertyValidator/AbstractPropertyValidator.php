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

abstract class AbstractPropertyValidator implements PropertyValidatorInterface
{
    /** @var string  */
    protected $sLastErrMsg = '';

    /**
     * @inheritDoc
     */
    abstract public function isValid($sProperty, JobEntityInterface $oJobEntity);
    
    /**
     * @inheritDoc
     */
    public function getLastErrorMessage()
    {
        return $this->sLastErrMsg;
    }

    /**
     * @param boolean $bIsValid
     * @param string $sProperty
     * @param string $sErrMsgTpl
     * @return bool
     */
    protected function returnIsValidHelper($bIsValid, $sProperty, $sErrMsgTpl)
    {
        if (!$bIsValid)
        {
            $this->sLastErrMsg = sprintf($sErrMsgTpl, $sProperty);
            return false;
        }

        $this->sLastErrMsg = '';
        return true;
    }
}