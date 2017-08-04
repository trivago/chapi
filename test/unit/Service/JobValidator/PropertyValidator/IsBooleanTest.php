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
        $_oPropertyValidator = new IsBoolean();
        
        $this->handleValidTestCase($_oPropertyValidator, 'highPriority', true);
        $this->handleValidTestCase($_oPropertyValidator, 'shell', false);
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new IsBoolean();

        $this->handleInvalidTestCase($_oPropertyValidator, 'highPriority', 1);
        $this->handleInvalidTestCase($_oPropertyValidator, 'shell', null);
        $this->handleInvalidTestCase($_oPropertyValidator, 'disabled', 'foo');
        $this->handleInvalidTestCase($_oPropertyValidator, 'softError', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new IsBoolean();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'highPriority', true, 'foo');
    }
}
