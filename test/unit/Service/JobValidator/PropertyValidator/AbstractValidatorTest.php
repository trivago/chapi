<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-11
 *
 */

namespace unit\Service\JobValidator\PropertyValidator;


use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobValidator\PropertyValidatorInterface;
use ChapiTest\src\TestTraits\JobEntityTrait;

abstract class AbstractValidatorTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /**
     * @param PropertyValidatorInterface $oValidator
     * @param string $sProperty
     * @param mixed $mValidValue
     * @param mixed $mInvalidValue
     * @param JobEntity|null $oJobEntity
     */
    protected function handleErrorMessageMultiTestCase(
        PropertyValidatorInterface $oValidator,
        $sProperty,
        $mValidValue,
        $mInvalidValue,
        JobEntity $oJobEntity = null
    )
    {
        if (is_null($oJobEntity))
        {
            $oJobEntity = $this->getValidScheduledJobEntity();
        }
        
        $oJobEntity->{$sProperty} = $mInvalidValue;
        $this->assertFalse($oValidator->isValid($sProperty, $oJobEntity));
        $this->assertContains($sProperty, $oValidator->getLastErrorMessage());

        $oJobEntity->{$sProperty} = $mValidValue;
        $this->assertTrue($oValidator->isValid($sProperty, $oJobEntity));
        $this->assertEmpty($oValidator->getLastErrorMessage());
    }

    /**
     * @param PropertyValidatorInterface $oValidator
     * @param string $sProperty
     * @param mixed $mValidValue
     * @param JobEntity|null $oJobEntity
     */
    protected function handleValidTestCase(
        PropertyValidatorInterface $oValidator,
        $sProperty,
        $mValidValue,
        JobEntity $oJobEntity = null
    )
    {
        if (is_null($oJobEntity))
        {
            $oJobEntity = $this->getValidScheduledJobEntity();    
        }
        
        $oJobEntity->{$sProperty} = $mValidValue;
        $this->assertTrue($oValidator->isValid($sProperty, $oJobEntity));
        $this->assertEmpty($oValidator->getLastErrorMessage());
    }

    /**
     * @param PropertyValidatorInterface $oValidator
     * @param $sProperty
     * @param $mInvalidValue
     * @param JobEntity|null $oJobEntity
     */
    protected function handleInvalidTestCase(
        PropertyValidatorInterface $oValidator,
        $sProperty,
        $mInvalidValue,
        JobEntity $oJobEntity = null
    )
    {
        if (is_null($oJobEntity))
        {
            $oJobEntity = $this->getValidScheduledJobEntity();
        }
        
        $oJobEntity->{$sProperty} = $mInvalidValue;
        $this->assertFalse($oValidator->isValid($sProperty, $oJobEntity));
        $this->assertContains($sProperty, $oValidator->getLastErrorMessage());
    }
}