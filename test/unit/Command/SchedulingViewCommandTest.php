<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-11
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace unit\Command;

use Chapi\Commands\SchedulingViewCommand;
use Chapi\Component\DatePeriod\DatePeriodFactory;
use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Service\Chronos\JobStatsServiceInterface;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;
use ChapiTest\src\TestTraits\JobStatsEntityTrait;

class SchedulingViewCommandTest extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;
    use JobStatsEntityTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobStatsService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobDependencyService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oDatePeriodFactory;

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->oJobStatsService = $this->prophesize('Chapi\Service\Chronos\JobStatsServiceInterface');
        $this->oJobDependencyService = $this->prophesize('Chapi\Service\JobDependencies\JobDependencyServiceInterface');
        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oDatePeriodFactory = new DatePeriodFactory();

        $this->oContainer->get(Argument::exact(JobStatsServiceInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->oJobStatsService->reveal());
        $this->oContainer->get(Argument::exact(JobDependencyServiceInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->oJobDependencyService->reveal());
        $this->oContainer->get(Argument::exact(JobRepositoryInterface::DIC_NAME_CHRONOS))->shouldBeCalledTimes(1)->willReturn($this->oJobRepositoryChronos->reveal());
        $this->oContainer->get(Argument::exact(DatePeriodFactoryInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->oDatePeriodFactory);
    }

    public function testProcessWithoutJobInput()
    {
        $this->oInput->getOption(Argument::exact('starttime'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oInput->getOption(Argument::exact('endtime'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->oJobStatsService->getJobStats(Argument::type('string'))->willReturn($this->createValidJobStatsEntity());
        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->oJobDependencyService->getChildJobs(Argument::exact('JobA'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->shouldBeCalledTimes(1)->willReturn(['JobB']);
        $this->oJobDependencyService->getChildJobs(Argument::type('string'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->willReturn([]);

        $_oCommand = new SchedulingViewCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }

    public function testProcessWithJobInput($sStartTime = '+2 hours', $sEndTime = '+4 hours')
    {
        $this->oInput->getOption(Argument::exact('starttime'))->shouldBeCalledTimes(1)->willReturn($sStartTime);
        $this->oInput->getOption(Argument::exact('endtime'))->shouldBeCalledTimes(1)->willReturn($sEndTime);

        $this->oJobStatsService->getJobStats(Argument::type('string'))->willReturn($this->createValidJobStatsEntity());
        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->oJobDependencyService->getChildJobs(Argument::type('string'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->willReturn([]);

        $_oCommand = new SchedulingViewCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }

    public function testProcessWithJobInput2()
    {
        $this->testProcessWithJobInput('-1 hours', null);
    }
}

class SchedulingViewCommandDummy extends SchedulingViewCommand
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