<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-07
 *
 */


namespace unit\Command;

use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class InfoCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryChronos;

    protected function setUp(): void
    {
        $this->setUpCommandDependencies();

        $this->jobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->container->get(Argument::exact(JobRepositoryInterface::DIC_NAME_CHRONOS))->shouldBeCalledTimes(1)->willReturn($this->jobRepositoryChronos->reveal());
    }

    public function testProcessWithoutOptions()
    {
        $jobEntity = $this->getValidScheduledJobEntity();
        $this->input->getArgument(Argument::exact('jobName'))->shouldBeCalledTimes(1)->willReturn('JobA');
        $this->jobRepositoryChronos->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntity);

        $command = new InfoCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertSame(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        // Spies
        $this->output->writeln(Argument::containingString('JobA'))->shouldHaveBeenCalled();
        foreach ($jobEntity as $_sKey => $_mValue) {
            $this->output->writeln(Argument::containingString($_sKey))->shouldHaveBeenCalled();
        }
    }
}
