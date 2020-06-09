<?php
/**
 * @package: cahpi
 *
 * @author:  msiebeneicher
 * @since:   2015-11-25
 */


namespace unit\Command;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\AppEntityTrait;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ListCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;
    use JobEntityTrait;
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryChronos;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryMarathon;

    protected function setUp(): void
    {
        $this->setUpCommandDependencies();

        $this->jobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobRepositoryMarathon = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->container->get(Argument::exact(JobRepositoryInterface::DIC_NAME_CHRONOS))->shouldBeCalledTimes(1)->willReturn($this->jobRepositoryChronos->reveal());
        $this->container->get(Argument::exact(JobRepositoryInterface::DIC_NAME_MARATHON))->shouldBeCalledTimes(1)->willReturn($this->jobRepositoryMarathon->reveal());
        // $this->oOutput->write(Argument::type('string'))->will(function($args){ var_dump($args); }); # Debug output
    }

    public function testProcessWithoutOptions()
    {
        $this->input->getOption(Argument::exact('onlyFailed'))->willReturn(false)->shouldBeCalledTimes(1);
        $this->input->getOption(Argument::exact('onlyDisabled'))->willReturn(false)->shouldBeCalledTimes(1);

        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->jobRepositoryMarathon->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createAppCollection(['/main/id1']));

        $command = new ListCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        // Spies
        $this->output->writeln(Argument::containingString('JobA'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobB'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobC'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('/main/id1'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('ok'))->shouldHaveBeenCalledTimes(4); // four jobs are all ok
    }

    public function testProcessWithFailingOption()
    {
        $this->input->getOption(Argument::exact('onlyFailed'))->willReturn(true)->shouldBeCalledTimes(1);
        $this->input->getOption(Argument::exact('onlyDisabled'))->willReturn(false)->shouldBeCalledTimes(1);

        $collection = $this->createJobCollection();
        /** @var ChronosJobEntity $entityB */
        $entityB = $collection->offsetGet('JobB');

        $entityB->errorCount = 10;
        $entityB->successCount = 100;
        $entityB->errorsSinceLastSuccess = 5;

        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($collection);
        $this->jobRepositoryMarathon->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createAppCollection(['/main/id1']));

        $command = new ListCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        // Spies
        $this->output->writeln(Argument::containingString('JobA'))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobB'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobC'))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::containingString('/main/id1'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('errors rate: 10%'))->shouldHaveBeenCalledTimes(1);
        $this->output->writeln(Argument::containingString('errors since last success:5'))->shouldHaveBeenCalledTimes(1);
    }

    public function testProcessWithDisabledOption()
    {
        $this->input->getOption(Argument::exact('onlyFailed'))->willReturn(false)->shouldBeCalledTimes(1);
        $this->input->getOption(Argument::exact('onlyDisabled'))->willReturn(true)->shouldBeCalledTimes(1);

        $collection = $this->createJobCollection();
        /** @var ChronosJobEntity $entityB */
        $entityB = $collection->offsetGet('JobC');
        $entityB->disabled = true;

        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($collection);
        $this->jobRepositoryMarathon->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createAppCollection(['/main/id1']));

        $command = new ListCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertEquals(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );

        // Spies
        $this->output->writeln(Argument::containingString('JobA'))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobB'))->shouldNotHaveBeenCalled();
        $this->output->writeln(Argument::containingString('JobC'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('/main/id1'))->shouldHaveBeenCalled();
        $this->output->writeln(Argument::containingString('disabled'))->shouldHaveBeenCalledTimes(1);
    }
}
