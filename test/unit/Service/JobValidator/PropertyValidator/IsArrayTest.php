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
use ChapiTest\src\TestTraits\JobEntityTrait;

class IsArrayTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $_oIsArray = new IsArray();
        $this->handleValidTestCase($_oIsArray, 'parents', [1, 2, 3]);
    }

    public function testIsValidFailure()
    {
        $_oIsArray = new IsArray();
        $this->handleInvalidTestCase($_oIsArray, 'parents', 'foo');
        $this->handleInvalidTestCase($_oIsArray, 'parents', new \stdClass());
        $this->handleInvalidTestCase($_oIsArray, 'parents', 1);
        $this->handleInvalidTestCase($_oIsArray, 'parents', false);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oIsArray = new IsArray();
        $this->handleErrorMessageMultiTestCase($_oIsArray, 'parents', [1, 2, 3], 1);
    }
}