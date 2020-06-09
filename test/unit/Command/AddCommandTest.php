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
use Chapi\Component\Command\JobUtilsInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use Prophecy\Argument;

class AddCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;

    private $jobIndexServiceInterface;

    public function setUp()
    {
        $this->jobIndexServiceInterface = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
    }

    public function testProcessWithJobInput()
    {
        $this->setUpCommandDependencies();

        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['JobA'])->shouldBeCalledTimes(1);

        $this->jobIndexServiceInterface->addJobs(Argument::any())->shouldBeCalledTimes(1);

        $this->container->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->jobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $command = new AddCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }

    public function testProcessWithWildcard()
    {
        $this->setUpCommandDependencies();

        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $this->jobIndexServiceInterface->addJobs(Argument::exact(['JobA', 'JobB', 'JobC']))->shouldBeCalledTimes(1);

        $this->container->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->jobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $jobComparisonInterface = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $jobComparisonInterface->getRemoteMissingJobs()->willReturn(['JobA'])->shouldBeCalledTimes(1);
        $jobComparisonInterface->getLocalMissingJobs()->willReturn(['JobB'])->shouldBeCalledTimes(1);
        $jobComparisonInterface->getLocalJobUpdates()->willReturn(['JobC'])->shouldBeCalledTimes(1);

        $this->container->get(Argument::exact(JobComparisonInterface::DIC_NAME))
            ->willReturn($jobComparisonInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $command = new AddCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }
}
