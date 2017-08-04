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
        $command = new Command();
        $this->handleValidTestCase($command, 'command', 'foo');

        $jobEntity = $this->getValidContainerJobEntity();
        $this->handleValidTestCase($command, 'command', '', $jobEntity);
    }

    public function testIsValidFailure()
    {
        $command = new Command();

        $this->handleInvalidTestCase($command, 'command', '');
        $this->handleInvalidTestCase($command, 'command', 1);
        $this->handleInvalidTestCase($command, 'command', [1, 2]);
    }

    public function testGetLastErrorMessageMulti()
    {
        $command = new Command();
        $this->handleErrorMessageMultiTestCase($command, 'command', 'foo', '');
    }
}
