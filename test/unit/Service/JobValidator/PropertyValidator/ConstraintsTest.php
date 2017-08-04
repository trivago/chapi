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
        $validator = new Constraints();
        
        $this->handleValidTestCase($validator, 'constraints', []);
        $this->handleValidTestCase($validator, 'constraints', null);
        $this->handleValidTestCase($validator, 'constraints', [[1, 2, 3], ['a', 'b', 'c']]);
    }

    public function testIsValidFailure()
    {
        $validator = new Constraints();

        $this->handleInvalidTestCase($validator, 'constraints', 'foo');
        $this->handleInvalidTestCase($validator, 'constraints', 1);
        $this->handleInvalidTestCase($validator, 'constraints', [[1, 2]]);
    }

    public function testGetLastErrorMessageMulti()
    {
        $validator = new Constraints();
        $this->handleErrorMessageMultiTestCase($validator, 'constraints', [], 'foo');
    }
}
