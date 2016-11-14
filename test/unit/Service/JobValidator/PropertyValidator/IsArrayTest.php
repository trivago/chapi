<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;


use Chapi\Service\JobValidator\PropertyValidator\IsArray;

class IsArrayTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $_oPropertyValidator = new IsArray();
        $this->handleValidTestCase($_oPropertyValidator, 'parents', [1, 2, 3]);
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new IsArray();
        $this->handleInvalidTestCase($_oPropertyValidator, 'parents', 'foo');
        $this->handleInvalidTestCase($_oPropertyValidator, 'parents', new \stdClass());
        $this->handleInvalidTestCase($_oPropertyValidator, 'parents', 1);
        $this->handleInvalidTestCase($_oPropertyValidator, 'parents', false);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new IsArray();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'parents', [1, 2, 3], 1);
    }
}