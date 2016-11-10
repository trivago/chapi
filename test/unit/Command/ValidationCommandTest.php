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

use Chapi\Commands\ValidationCommand;
use Chapi\Component\Command\JobUtilsInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobRepository\JobValidatorServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use ChapiTest\src\TestTraits\CommandTestTrait;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ValidationCommandTest extends \PHPUnit_Framework_TestCase
{
    use CommandTestTrait;
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobEntityValidatorService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryLocale;

    public function setUp()
    {
        $this->setUpCommandDependencies();

        $this->oJobEntityValidatorService = $this->prophesize('Chapi\Service\JobRepository\JobValidatorServiceInterface');

        $this->oJobRepositoryLocale = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
    }

    public function testValidationWithWildcardSuccess()
    {
        $this->oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $_oJobCollection = $this->createJobCollection();
        $this->oJobRepositoryLocale->getJobs()->shouldBeCalledTimes(1)->willReturn($_oJobCollection);
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobA'));
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobB'));
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobC'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobC'));

        $this->oJobEntityValidatorService->isEntityValid(Argument::type('Chapi\Entity\Chronos\JobEntity'))->shouldBeCalledTimes(3)->willReturn(true);

        $this->oContainer
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM))
            ->shouldBeCalled()
            ->willReturn($this->oJobRepositoryLocale->reveal())
        ;

        $this->oContainer
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->oJobEntityValidatorService->reveal())
        ;

        $_oCommand = new ValidationCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            0,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }

    public function testValidationWithWildcardFailure()
    {
        $this->oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['*'])->shouldBeCalledTimes(1);

        $_oJobCollection = $this->createJobCollection();

        $this->oJobRepositoryLocale->getJobs()->shouldBeCalledTimes(1)->willReturn($_oJobCollection);
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobA'));
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobB'));
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobC'))->shouldBeCalledTimes(1)->willReturn($_oJobCollection->offsetGet('JobC'));

        $this->oJobEntityValidatorService->isEntityValid(Argument::type('Chapi\Entity\Chronos\JobEntity'))->shouldBeCalledTimes(3)->willReturn(false);
        $this->oJobEntityValidatorService->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\JobEntity'))->shouldBeCalledTimes(3)->willReturn(['epsilon', 'command']);

        $this->oContainer
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM))
            ->shouldBeCalled()
            ->willReturn($this->oJobRepositoryLocale->reveal())
        ;

        $this->oContainer
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->oJobEntityValidatorService->reveal())
        ;

        $_oCommand = new ValidationCommandDummy();
        $_oCommand::$oContainerDummy = $this->oContainer->reveal();

        $this->assertEquals(
            1,
            $_oCommand->run(
                $this->oInput->reveal(),
                $this->oOutput->reveal()
            )
        );
    }

    public function testValidationWithoutWildcardSuccess()
    {
        $this->oInput->getArgument(Argument::exact(JobUtilsInterface::ARGUMENT_JOBNAMES))->willReturn(['JobA'])->shouldBeCalledTimes(1);

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $this->oJobRepositoryLocale->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntity);

        $this->oJobEntityValidatorService->isEntityValid(Argument::exact($_oJobEntity))->shouldBeCalledTimes(1)->willReturn(true);

        $this->oContainer
            ->get(Argument::exact(JobRepositoryInterface::DIC_NAME_FILESYSTEM))
            ->shouldBeCalled()
            ->willReturn($this->oJobRepositoryLocale->reveal())
        ;

        $this->oContainer
            ->get(Argument::exact(JobValidatorServiceInterface::DIC_NAME))
            ->shouldBeCalled()
            ->willReturn($this->oJobEntityValidatorService->reveal())
        ;

        $_oCommand = new ValidationCommandDummy();
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

class ValidationCommandDummy extends ValidationCommand
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