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
    private $oLogger;

    public function setUp()
    {
        $this->oJobIndexService = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryServiceInterface');
        $this->oJobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryServiceInterface');
        $this->oJobComparisonBusinessCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
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
            $this->oLogger->reveal()
        );

        $this->assertNull($_oStoreJobBusinessCase->storeIndexedJobs());
    }
}