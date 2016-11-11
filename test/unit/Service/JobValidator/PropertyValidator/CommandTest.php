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
        
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->command = 'foo';
        $this->assertTrue($_oCommand->isValid('command', $_oJobEntity));
        $this->assertEmpty($_oCommand->getLastErrorMessage());

        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->command = '';
        $this->assertTrue($_oCommand->isValid('command', $_oJobEntity));
        $this->assertEmpty($_oCommand->getLastErrorMessage());
    }

    public function testIsValidFailure()
    {
        $_oCommand = new Command();

        $_oJobEntity = $this->getValidScheduledJobEntity();
        
        $_oJobEntity->command = '';
        $this->assertFalse($_oCommand->isValid('command', $_oJobEntity));
        $this->assertContains('command', $_oCommand->getLastErrorMessage());

        $_oJobEntity->command = [1, 2];
        $this->assertFalse($_oCommand->isValid('command', $_oJobEntity));
        $this->assertContains('command', $_oCommand->getLastErrorMessage());
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oCommand = new Command();
        $this->handleTestGetLastErrorMessageMulti($_oCommand, 'command', 'foo', '');
    }
}