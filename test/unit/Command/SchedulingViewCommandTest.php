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

use Chapi\Component\DatePeriod\DatePeriodFactory;
use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Service\Chronos\JobStatsServiceInterface;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;
use ChapiTest\src\TestTraits\JobStatsEntityTrait;

class SchedulingViewCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;
    use JobStatsEntityTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobStatsService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobDependencyService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $datePeriodFactory;

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->jobStatsService = $this->prophesize('Chapi\Service\Chronos\JobStatsServiceInterface');
        $this->jobDependencyService = $this->prophesize('Chapi\Service\JobDependencies\JobDependencyServiceInterface');
        $this->jobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->datePeriodFactory = new DatePeriodFactory();

        $this->container->get(Argument::exact(JobStatsServiceInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->jobStatsService->reveal());
        $this->container->get(Argument::exact(JobDependencyServiceInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->jobDependencyService->reveal());
        $this->container->get(Argument::exact(JobRepositoryInterface::DIC_NAME_CHRONOS))->shouldBeCalledTimes(1)->willReturn($this->jobRepositoryChronos->reveal());
        $this->container->get(Argument::exact(DatePeriodFactoryInterface::DIC_NAME))->shouldBeCalledTimes(1)->willReturn($this->datePeriodFactory);
    }

    public function testProcessWithoutJobInput()
    {
        $this->input->getOption(Argument::exact('starttime'))->shouldBeCalledTimes(1)->willReturn(null);
        $this->input->getOption(Argument::exact('endtime'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->jobStatsService->getJobStats(Argument::type('string'))->willReturn($this->createValidJobStatsEntity());
        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->jobDependencyService->getChildJobs(Argument::exact('JobA'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->shouldBeCalledTimes(1)->willReturn(['JobB']);
        $this->jobDependencyService->getChildJobs(Argument::type('string'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->willReturn([]);

        $command = new SchedulingViewCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }

    public function testProcessWithJobInput($startTime = '+2 hours', $endTime = '+4 hours')
    {
        $this->input->getOption(Argument::exact('starttime'))->shouldBeCalledTimes(1)->willReturn($startTime);
        $this->input->getOption(Argument::exact('endtime'))->shouldBeCalledTimes(1)->willReturn($endTime);

        $this->jobStatsService->getJobStats(Argument::type('string'))->willReturn($this->createValidJobStatsEntity());
        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->jobDependencyService->getChildJobs(Argument::type('string'), JobDependencyServiceInterface::REPOSITORY_LOCAL)->willReturn([]);

        $command = new SchedulingViewCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }

    public function testProcessWithJobInput2()
    {
        $this->testProcessWithJobInput('-1 hours', null);
    }
}
