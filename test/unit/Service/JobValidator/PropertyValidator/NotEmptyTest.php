<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\NotEmpty;

class NotEmptyTest extends AbstractValidatorTest
{
    public function testIsValidSuccess()
    {
        $propertyValidator = new NotEmpty();

        $this->handleValidTestCase($propertyValidator, 'ownerName', 'owner');
        $this->handleValidTestCase($propertyValidator, 'description', 'foo bar');
    }

    public function testIsValidFailure()
    {
        $propertyValidator = new NotEmpty();

        $this->handleInvalidTestCase($propertyValidator, 'ownerName', '');
        $this->handleInvalidTestCase($propertyValidator, 'description', null);
        $this->handleInvalidTestCase($propertyValidator, 'ownerName', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $propertyValidator = new NotEmpty();
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'ownerName', 'owner', '');
    }
}
