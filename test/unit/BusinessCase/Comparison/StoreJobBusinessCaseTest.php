<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-23
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/18
 */


namespace unit\BusinessCase\Comparison;


use Chapi\BusinessCase\JobManagement\StoreJobBusinessCase;
use Chapi\Entity\Chronos\JobEntity;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class StoreJobBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobIndexService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryChronos;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobComparisonBusinessCase;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobDependencyService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    public function setUp()
    {
        $this->oJobIndexService = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobComparisonBusinessCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->oJobDependencyService = $this->prophesize('Chapi\Service\JobDependencies\JobDependencyServiceInterface');
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testStoreIndexedJobsSuccess()
    {
        $_sJobNameA = 'JobA';
        $_sJobNameB = 'JobB';
        $_sJobNameC = 'JobC';
        $_sJobNameD = 'JobD';

        $_aMissingJobs = [$_sJobNameA, $_sJobNameB];
        $_aLocalMissingJobs = [$_sJobNameC];
        $_aLocalJobUpdates = [$_sJobNameD];

        $_oJobEnetityB = $this->getValidScheduledJobEntity($_sJobNameB);
        $_oJobEnetityD = $this->getValidScheduledJobEntity($_sJobNameD);

        // general mocking
        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalled()
        ;

        $this->oLogger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        // add new jobs to chronos
        $this->oJobComparisonBusinessCase
            ->getChronosMissingJobs()
            ->willReturn($_aMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameA))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_sJobNameB))
            ->willReturn($_oJobEnetityB)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryChronos
            ->addJob(Argument::exact($_oJobEnetityB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->removeJob(Argument::exact($_sJobNameB))
            ->shouldBeCalledTimes(1)
        ;

        // delete missing jobs from chronos
        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($_aLocalMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameC))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryChronos
            ->removeJob(Argument::exact($_sJobNameC))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->removeJob(Argument::exact($_sJobNameC))
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($_aLocalJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameD))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_sJobNameD))
            ->willReturn($_oJobEnetityD)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryChronos
            ->updateJob(Argument::exact($_oJobEnetityD))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->removeJob(Argument::exact($_sJobNameD))
            ->shouldBeCalledTimes(1)
        ;

        // test
        $_oStoreJobBusinessCase = new StoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oJobDependencyService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertNull($_oStoreJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsFailureInAdding()
    {
        $_sJobNameA = 'JobA';
        $_sJobNameB = 'JobB';

        $_aMissingJobs = [$_sJobNameA, $_sJobNameB];
        $_aLocalMissingJobs = [];
        $_aLocalJobUpdates = [];

        $_oJobEnetityB = $this->getValidScheduledJobEntity($_sJobNameB);

        // general mocking
        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldNotBeCalled()
        ;

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1)
        ;

        // add new jobs to chronos
        $this->oJobComparisonBusinessCase
            ->getChronosMissingJobs()
            ->willReturn($_aMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameA))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact($_sJobNameB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_sJobNameB))
            ->willReturn($_oJobEnetityB)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobRepositoryChronos
            ->addJob(Argument::exact($_oJobEnetityB))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->oJobIndexService
            ->removeJob(Argument::exact($_sJobNameB))
            ->shouldNotBeCalled()
        ;

        // delete missing jobs from chronos
        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($_aLocalMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($_aLocalJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        // test
        $_oStoreJobBusinessCase = new StoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oJobDependencyService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertNull($_oStoreJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreJobsToLocalRepositorySuccess()
    {
        $_oJobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_oJobEntityA2 = clone $_oJobEntityA1;
        $_oJobEntityA2->disabled = true;

        $_oJobEntityB1 = $this->getValidDependencyJobEntity('JobB', 'JobC');

        $this->oJobRepositoryChronos->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntityA1);
        $this->oJobRepositoryChronos->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($_oJobEntityB1);

        $this->oJobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntityA2);
        $this->oJobRepositoryLocal->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn(new JobEntity());

        $this->oJobRepositoryLocal->addJob(Argument::exact($_oJobEntityB1))->shouldBeCalledTimes(1)->willReturn(true);

        $this->oJobComparisonBusinessCase->getJobDiff(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn(['disabled'=>'div string']);

        $this->oJobRepositoryLocal->updateJob(Argument::exact($_oJobEntityA1))->shouldBeCalledTimes(1)->willReturn(true);

        $this->oJobIndexService->removeJob(Argument::exact('JobA'))->shouldBeCalledTimes(1);

        // test
        $_oStoreJobBusinessCase = new StoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oJobDependencyService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertNull($_oStoreJobBusinessCase->storeJobsToLocalRepository(['JobA', 'JobB'], true));

        // spy
        $this->oLogger->error(Argument::type('string'))->shouldNotBeCalled();
        $this->oLogger->notice(Argument::type('string'))->shouldBeCalled();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStoreJobsToLocalRepositoryFailureBecauseJobExists()
    {
        $_oJobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $_oJobEntityA2 = clone $_oJobEntityA1;
        $_oJobEntityA2->disabled = true;


        $this->oJobRepositoryChronos->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntityA1);
        $this->oJobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_oJobEntityA2);

        $this->oJobComparisonBusinessCase->getJobDiff(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn(['disabled'=>'div string']);

        $this->oJobRepositoryLocal->updateJob(Argument::exact($_oJobEntityA1))->shouldNotBeCalled();

        $this->oJobIndexService->removeJob(Argument::exact('JobA'))->shouldNotBeCalled();

        // test
        $_oStoreJobBusinessCase = new StoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryChronos->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oJobDependencyService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertNull($_oStoreJobBusinessCase->storeJobsToLocalRepository(['JobA']));

        // spy
        $this->oLogger->error(Argument::type('string'))->shouldBeCalled();
        $this->oLogger->notice(Argument::type('string'))->shouldNotBeCalled();
    }
}