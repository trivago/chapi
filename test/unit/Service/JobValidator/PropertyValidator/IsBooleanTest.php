<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\IsBoolean;

class IsBooleanTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $propertyValidator = new IsBoolean();
        
        $this->handleValidTestCase($propertyValidator, 'highPriority', true);
        $this->handleValidTestCase($propertyValidator, 'shell', false);
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new IsBoolean();

        $this->handleInvalidTestCase($propertyValidator, 'highPriority', 1);
        $this->handleInvalidTestCase($propertyValidator, 'shell', null);
        $this->handleInvalidTestCase($propertyValidator, 'disabled', 'foo');
        $this->handleInvalidTestCase($propertyValidator, 'softError', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new IsBoolean();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'highPriority', true, 'foo');
    }
}
