<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-07
 *
 */


namespace unit\Command;


use Chapi\Commands\InfoCommand;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class InfoCommandTest  extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryChronos;

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oContainer->get(Argument::exact(JobRepositoryInterface::DIC_NAME_CHRONOS))->shouldBeCalledTimes(1)->willReturn($this->oJobRepositoryChronos->reveal());
    }

    public function testProcessWithoutOptions()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $this->oInput->getArgument(Argument::exact('jobName'))->shouldBeCalledTimes(1)->willReturn('JobA');
        $this->oJobRepositoryChronos->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntity);

        $_oCommand = new InfoCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        // Spies
        $this->oOutput->writeln(Argument::containingString('JobA'))->shouldHaveBeenCalled();
        foreach ($_oJobEntity as $_sKey => $_mValue)
        {
            $this->oOutput->writeln(Argument::containingString($_sKey))->shouldHaveBeenCalled();
        }
    }
}

class InfoCommandDummy extends InfoCommand
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