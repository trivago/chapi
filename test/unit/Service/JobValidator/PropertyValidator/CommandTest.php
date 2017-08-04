<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-11
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Service\JobValidator\PropertyValidator\Command;
use ChapiTest\src\TestTraits\JobEntityTrait;

class CommandTest extends AbstractValidatorTest
{
    use JobEntityTrait;

    public function testIsValidSuccess()
    {
        $_oCommand = new Command();
        $this->handleValidTestCase($_oCommand, 'command', 'foo');

        $_oJobEntity = $this->getValidContainerJobEntity();
        $this->handleValidTestCase($_oCommand, 'command', '', $_oJobEntity);
    }

    public function testIsValidFailure()
    {
        $_oCommand = new Command();

        $this->handleInvalidTestCase($_oCommand, 'command', '');
        $this->handleInvalidTestCase($_oCommand, 'command', 1);
        $this->handleInvalidTestCase($_oCommand, 'command', [1, 2]);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oCommand = new Command();
        $this->handleErrorMessageMultiTestCase($_oCommand, 'command', 'foo', '');
    }
}
