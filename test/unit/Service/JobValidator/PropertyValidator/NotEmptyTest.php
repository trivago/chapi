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
        $_oPropertyValidator = new NotEmpty();

        $this->handleValidTestCase($_oPropertyValidator, 'ownerName', 'owner');
        $this->handleValidTestCase($_oPropertyValidator, 'description', 'foo bar');
    }

    public function testIsValidFailure()
    {
        $_oPropertyValidator = new NotEmpty();

        $this->handleInvalidTestCase($_oPropertyValidator, 'ownerName', '');
        $this->handleInvalidTestCase($_oPropertyValidator, 'description', null);
        $this->handleInvalidTestCase($_oPropertyValidator, 'ownerName', []);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oPropertyValidator = new NotEmpty();
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'ownerName', 'owner', '');
    }
}