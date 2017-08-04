<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\Retries;

class RetriesTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $propertyValidator = new Retries();

        $this->handleValidTestCase($propertyValidator, 'retries', 0);
        $this->handleValidTestCase($propertyValidator, 'retries', 1);
        $this->handleValidTestCase($propertyValidator, 'retries', 10);
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new Retries();

        $this->handleInvalidTestCase($propertyValidator, 'retries', -1);
        $this->handleInvalidTestCase($propertyValidator, 'retries', null);
        $this->handleInvalidTestCase($propertyValidator, 'retries', false);
        $this->handleInvalidTestCase($propertyValidator, 'retries', 'foo');
        $this->handleInvalidTestCase($propertyValidator, 'retries', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new Retries();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'retries', 1, -10);
    }
}
