<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-15
 *
 */

namespace unit\Command;

use Chapi\Component\Command\JobUtilsInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use Prophecy\Argument;

class ResetCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;

    private $jobIndexServiceInterface;

    protected function setUp(): void
    {
        $this->jobIndexServiceInterface = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
    }

    public function testProcessWithJobInput()
    {
        $this->setUpCommandDependencies();

        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['JobA'])->shouldBeCalledTimes(1);

        $this->jobIndexServiceInterface->removeJobs(Argument::any())->shouldBeCalledTimes(1);

        $this->container->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->jobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $command = new ResetCommandDummy();
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

        $this->jobIndexServiceInterface->resetJobIndex()->shouldBeCalledTimes(1);

        $this->container->get(Argument::exact(JobIndexServiceInterface::DIC_NAME))
            ->willReturn($this->jobIndexServiceInterface->reveal())
            ->shouldBeCalledTimes(1)
        ;

        $_oCommand = new ResetCommandDummy();
        $_oCommand::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }
}
