<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-11
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\Constraints;
use ChapiTest\src\TestTraits\JobEntityTrait;

class ConstraintsTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $_oValidator = new Constraints();
        
        $this->handleValidTestCase($_oValidator, 'constraints', []);
        $this->handleValidTestCase($_oValidator, 'constraints', null);
        $this->handleValidTestCase($_oValidator, 'constraints', [[1, 2, 3], ['a', 'b', 'c']]);
    }

    public function testIsValidFailure()
    {
        $_oValidator = new Constraints();

        $this->handleInvalidTestCase($_oValidator, 'constraints', 'foo');
        $this->handleInvalidTestCase($_oValidator, 'constraints', 1);
        $this->handleInvalidTestCase($_oValidator, 'constraints', [[1, 2]]);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oValidator = new Constraints();
        $this->handleErrorMessageMultiTestCase($_oValidator, 'constraints', [], 'foo');
    }
}
