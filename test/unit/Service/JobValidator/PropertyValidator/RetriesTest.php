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
        $_oPropertyValidator = new Retries();

        $this->handleValidTestCase($_oPropertyValidator, 'retries', 0);
        $this->handleValidTestCase($_oPropertyValidator, 'retries', 1);
        $this->handleValidTestCase($_oPropertyValidator, 'retries', 10);
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new Retries();

        $this->handleInvalidTestCase($_oPropertyValidator, 'retries', -1);
        $this->handleInvalidTestCase($_oPropertyValidator, 'retries', null);
        $this->handleInvalidTestCase($_oPropertyValidator, 'retries', false);
        $this->handleInvalidTestCase($_oPropertyValidator, 'retries', 'foo');
        $this->handleInvalidTestCase($_oPropertyValidator, 'retries', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new Retries();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'retries', 1, -10);
    }
}