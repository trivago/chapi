<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-02
 *
 */

namespace unit\Command;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Commands\AddCommand;
use Chapi\Component\Command\JobUtilsInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use Prophecy\Argument;

class AddCommandTest extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;

    private $oJobIndexServiceInterface;

    public function setUp()
    {
        $this->oJobIndexServiceInterface = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
    }

    public function testProcessWithJobInput()
    {
        $this->setUpCommandDependencies();

        $this->oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['JobA'])->shouldBeCalledTimes(1);

        $this->oJobIndexServiceInterface->addJobs(Argument::any())->shouldBeCalledTimes(1);

        $this->oContainer->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->oJobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $_oCommand = new AddCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }

    public function testProcessWithWildcard()
    {
        $this->setUpCommandDependencies();

        $this->oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $this->oJobIndexServiceInterface->addJobs(Argument::exact(['JobA', 'JobB', 'JobC']))->shouldBeCalledTimes(1);

        $this->oContainer->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->oJobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $_oJobComparisonInterface = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $_oJobComparisonInterface->getRemoteMissingJobs()->willReturn(['JobA'])->shouldBeCalledTimes(1);
        $_oJobComparisonInterface->getLocalMissingJobs()->willReturn(['JobB'])->shouldBeCalledTimes(1);
        $_oJobComparisonInterface->getLocalJobUpdates()->willReturn(['JobC'])->shouldBeCalledTimes(1);

        $this->oContainer->get(Argument::exact(JobComparisonInterface::DIC_NAME))
            ->willReturn($_oJobComparisonInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $_oCommand = new AddCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }
}

class AddCommandDummy extends AddCommand
{
    public static $oContainerDummy;

    protected function getContainer()
    {
        return self::$oContainerDummy;
    }

    protected function isAppRunable()
    {
        return true;
    }
}
