<?php
/**
 * @package: cahpi
 *
 * @author:  msiebeneicher
 * @since:   2015-11-25
 */


namespace unit\Command;


use Chapi\Commands\ListCommand;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ListCommandTest extends \PHPUnit_Framework_TestCase
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

        // $this->oOutput->write(Argument::type('string'))->will(function($args){ var_dump($args); }); # Debug output
    }

    public function testProcessWithoutOptions()
    {
        $this->oInput->getOption(Argument::exact('onlyFailed'))->willReturn(false)->shouldBeCalledTimes(1);
        $this->oInput->getOption(Argument::exact('onlyDisabled'))->willReturn(false)->shouldBeCalledTimes(1);

        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());


        $_oCommand = new ListCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        // Spies
        $this->oOutput->write(Argument::containingString('JobA'))->shouldBeCalled();
        $this->oOutput->write(Argument::containingString('JobB'))->shouldBeCalled();
        $this->oOutput->write(Argument::containingString('JobC'))->shouldBeCalled();
        $this->oOutput->write(Argument::containingString('ok'))->shouldBeCalledTimes(3); // three jobs are all ok

    }

    public function testProcessWithFailingOption()
    {
        $this->oInput->getOption(Argument::exact('onlyFailed'))->willReturn(true)->shouldBeCalledTimes(1);
        $this->oInput->getOption(Argument::exact('onlyDisabled'))->willReturn(false)->shouldBeCalledTimes(1);

        $_oCollection = $this->createJobCollection();
        /** @var JobEntity $_oEntityB */
        $_oEntityB = $_oCollection->offsetGet('JobB');

        $_oEntityB->errorCount = 10;
        $_oEntityB->successCount = 100;
        $_oEntityB->errorsSinceLastSuccess = 5;

        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($_oCollection);

        $_oCommand = new ListCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        // Spies
        $this->oOutput->write(Argument::containingString('JobA'))->shouldNotBeCalled();
        $this->oOutput->write(Argument::containingString('JobB'))->shouldBeCalled();
        $this->oOutput->write(Argument::containingString('JobC'))->shouldNotBeCalled();
        $this->oOutput->write(Argument::containingString('errors rate: 10%'))->shouldBeCalledTimes(1);
        $this->oOutput->write(Argument::containingString('errors since last success:5'))->shouldBeCalledTimes(1);
    }

    public function testProcessWithDisabledOption()
    {
        $this->oInput->getOption(Argument::exact('onlyFailed'))->willReturn(false)->shouldBeCalledTimes(1);
        $this->oInput->getOption(Argument::exact('onlyDisabled'))->willReturn(true)->shouldBeCalledTimes(1);

        $_oCollection = $this->createJobCollection();
        /** @var JobEntity $_oEntityB */
        $_oEntityB = $_oCollection->offsetGet('JobC');
        $_oEntityB->disabled = true;

        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($_oCollection);

        $_oCommand = new ListCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );

        // Spies
        $this->oOutput->write(Argument::containingString('JobA'))->shouldNotBeCalled();
        $this->oOutput->write(Argument::containingString('JobB'))->shouldNotBeCalled();
        $this->oOutput->write(Argument::containingString('JobC'))->shouldBeCalled();
        $this->oOutput->write(Argument::containingString('disabled'))->shouldBeCalledTimes(1);
    }
}

class ListCommandDummy extends ListCommand
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