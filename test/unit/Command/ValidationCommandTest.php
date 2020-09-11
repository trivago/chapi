<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 * @link:    http://
 */

namespace unit\Command;

use Chapi\Component\Command\JobUtilsInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ValidationCommandTest extends \PHPUnit\Framework\TestCase
{
    use CommandTestTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobEntityValidatorService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryLocal;

    protected function setUp(): void
    {
        $this->setUpCommandDependencies();

        $this->jobEntityValidatorService = $this->prophesize('Chapi\Service\JobValidator\JobValidatorServiceInterface');

        $this->jobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
    }

    public function testValidationWithWildcardSuccess()
    {
        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $jobCollection = $this->createJobCollection();
        $this->jobRepositoryLocal->getJobs()->shouldBeCalledTimes(1)->willReturn($jobCollection);
        $this->jobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobA'));
        $this->jobRepositoryLocal->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobB'));
        $this->jobRepositoryLocal->getJob(Argument::exact('JobC'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobC'));

        $this->jobEntityValidatorService->isEntityValid(Argument::type('Chapi\Entity\JobEntityInterface'))->shouldBeCalledTimes(3)->willReturn(true);

        $this->container
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM_CHRONOS))
            ->shouldBeCalled()
            ->willReturn($this->jobRepositoryLocal->reveal())
        ;

        $this->container
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->jobEntityValidatorService->reveal())
        ;

        $command = new ValidationCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertSame(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }

    public function testValidationWithWildcardFailure()
    {
        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $jobCollection = $this->createJobCollection();

        $this->jobRepositoryLocal->getJobs()->shouldBeCalledTimes(1)->willReturn($jobCollection);
        $this->jobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobA'));
        $this->jobRepositoryLocal->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobB'));
        $this->jobRepositoryLocal->getJob(Argument::exact('JobC'))->shouldBeCalledTimes(1)->willReturn($jobCollection->offsetGet('JobC'));

        $this->jobEntityValidatorService->isEntityValid(Argument::type('Chapi\Entity\JobEntityInterface'))->shouldBeCalledTimes(3)->willReturn(false);
        $this->jobEntityValidatorService->getInvalidProperties(Argument::type('Chapi\Entity\JobEntityInterface'))->shouldBeCalledTimes(3)->willReturn(['epsilon', 'command']);

        $this->container
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM_CHRONOS))
            ->shouldBeCalled()
            ->willReturn($this->jobRepositoryLocal->reveal())
        ;

        $this->container
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->jobEntityValidatorService->reveal())
        ;

        $command = new ValidationCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertSame(
            1,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }

    public function testValidationWithoutWildcardSuccess()
    {
        $this->input->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['JobA'])->shouldBeCalledTimes(1);

        $jobEntity = $this->getValidScheduledJobEntity();
        $this->jobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntity);

        $this->jobEntityValidatorService->isEntityValid(Argument::exact($jobEntity))->shouldBeCalledTimes(1)->willReturn(true);

        $this->container
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM_CHRONOS))
            ->shouldBeCalled()
            ->willReturn($this->jobRepositoryLocal->reveal())
        ;

        $this->container
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->jobEntityValidatorService->reveal())
        ;

        $command = new ValidationCommandDummy();
        $command::$containerDummy = $this->container->reveal();

        $this->assertSame(
            0,
            $command->run(
                $this->input->reveal(),
                $this->output->reveal()
            )
        );
    }
}
