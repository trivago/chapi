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
        $propertyValidator = new IsArray();
        $this->handleValidTestCase($propertyValidator, 'parents', [1, 2, 3]);
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new IsArray();
        $this->handleInvalidTestCase($propertyValidator, 'parents', 'foo');
        $this->handleInvalidTestCase($propertyValidator, 'parents', new \stdClass());
        $this->handleInvalidTestCase($propertyValidator, 'parents', 1);
        $this->handleInvalidTestCase($propertyValidator, 'parents', false);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new IsArray();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'parents', [1, 2, 3], 1);
    }
}
