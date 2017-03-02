<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-16
 *
 */

namespace unit\BusinessCase\JobManagement;


use Chapi\BusinessCase\JobManagement\MarathonStoreJobBusinessCase;
use ChapiTest\src\TestTraits\AppEntityTrait;
use Exception;
use InvalidArgumentException;
use Prophecy\Argument;

class MarathonStoreJobBusinessCaseTest extends \PHPUnit_Framework_TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobIndexService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryRemote;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobComparisonBusinessCase;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    public function setUp()
    {
        $this->oJobIndexService = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
        $this->oJobRepositoryRemote = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobComparisonBusinessCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testStoreJobsToLocalRepositoryWithAddSuccess()
    {
        $_aRemoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $_aLocalJobs = $this->createAppCollection(["/main/id1"]);

        $this->oJobRepositoryRemote
            ->getJobs()
            ->shouldNotBeCalled();

        $this->oJobRepositoryRemote
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aRemoteJobs["/main/id1"]);

        $this->oJobRepositoryRemote
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn($_aRemoteJobs["/main/id2"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id1"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn(null);

        $this->oJobRepositoryLocal
            ->addJob(Argument::exact($_aRemoteJobs["/main/id2"]))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalled();

        $this->oLogger
            ->error(Argument::any())
            ->shouldNotBeCalled();

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeJobsToLocalRepository(["/main/id1", "/main/id2"]);
    }

    public function testStoreJobsToLocalRepositoryWithAddFailure()
    {
        $_aRemoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $_aLocalJobs = $this->createAppCollection(["/main/id1"]);

        $this->oJobRepositoryRemote
            ->getJobs()
            ->shouldNotBeCalled();

        $this->oJobRepositoryRemote
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aRemoteJobs["/main/id1"]);

        $this->oJobRepositoryRemote
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn($_aRemoteJobs["/main/id2"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id1"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn(null);

        $this->oJobRepositoryLocal
            ->addJob($_aRemoteJobs["/main/id2"])
            ->shouldBeCalled()
            ->willReturn(false);

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalled();

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeJobsToLocalRepository(["/main/id1", "/main/id2"]);

    }

    public function testStoreJobsToLocalRepositoryWithUpdateSuccess()
    {
        $_aRemoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $_aLocalJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $_aLocalJobs["/main/id2"]->cpus = 4;

        $this->oJobRepositoryRemote
            ->getJobs()
            ->willReturn($_aRemoteJobs);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id1"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id2"]);


        $this->oJobComparisonBusinessCase
        ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
        ->willReturn([])
        ->shouldBeCalledTimes(1);

        $this->oJobComparisonBusinessCase
            ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediffs"])
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryLocal
            ->updateJob($_aRemoteJobs["/main/id2"])
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldNotBeCalled();

        $this->oJobIndexService
            ->removeJob(Argument::exact("/main/id2"))
            ->shouldBeCalledTimes(1);


        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeJobsToLocalRepository([], true);
    }

    public function testStoreJobsToLocalRepositoryWithRepositoryUpdateFailure()
    {
        $_aRemoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $_aLocalJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $_aLocalJobs["/main/id2"]->cpus = 4;

        $this->oJobRepositoryRemote
            ->getJobs()
            ->willReturn($_aRemoteJobs);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id1"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id2"]);


        $this->oJobComparisonBusinessCase
        ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
        ->willReturn([])
        ->shouldBeCalledTimes(1);

        $this->oJobComparisonBusinessCase
            ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediff"])
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryLocal
            ->updateJob($_aRemoteJobs["/main/id2"])
            ->willReturn(false)
            ->shouldBeCalledTimes(1);

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldNotBeCalled();

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $this->oJobIndexService
            ->removeJob(Argument::exact("/main/id2"))
            ->shouldBeCalledTimes(1);


        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeJobsToLocalRepository([], true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStoreJobsToLocalRepositoryWithUpdateFailureWithoutForce()
    {
        $_aRemoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $_aLocalJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $_aLocalJobs["/main/id2"]->cpus = 4;

        $this->oJobRepositoryRemote
            ->getJobs()
            ->willReturn($_aRemoteJobs);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id1"]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn($_aLocalJobs["/main/id2"]);


        $this->oJobComparisonBusinessCase
            ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id1"]->getKey()))
            ->willReturn([])
            ->shouldBeCalledTimes(1);

        $this->oJobComparisonBusinessCase
            ->getJobDiff(Argument::exact($_aRemoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediff"])
            ->shouldBeCalledTimes(1);


        $this->oJobRepositoryLocal
            ->updateJob(Argument::any())
            ->willReturn(true)
            ->shouldNotBeCalled();

        $this->oJobIndexService
            ->removeJob(Argument::any())
            ->shouldNotBeCalled();


        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeJobsToLocalRepository();
    }


    public function testStoreIndexedJobsWithRemoteMissingAppWithoutDependencySuccess()
    {
        $_oRemoteMising1 = $this->getValidMarathonAppEntity("/remote/missing1");


        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true);

        $this->oJobRepositoryLocal
            ->getJob("/remote/missing1")
            ->willReturn($_oRemoteMising1);

        $this->oJobRepositoryRemote
            ->addJob($_oRemoteMising1)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobIndexService
            ->removeJob(Argument::exact($_oRemoteMising1->getKey()))
            ->shouldBeCalledTimes(1);

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);


        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingAppWithDependencySuccess()
    {
        $_oRemoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $_oRemoteMissing1->dependencies = ["/remote/missing2"];
        $_oRemoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");

        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);


        $_oJobIndexServiceCopy = $this->oJobIndexService;
        $this->oJobIndexService
            ->removeJob(Argument::exact($_oRemoteMissing1->getKey()))
            ->will(function ($args) use ($_oJobIndexServiceCopy) {
                $_oJobIndexServiceCopy
                    ->isJobInIndex(Argument::exact("/remote/missing1"))
                    ->willReturn(false);
            })
            ->shouldBeCalled();

        $this->oJobIndexService
            ->removeJob(Argument::exact($_oRemoteMissing2->getKey()))
            ->will(function ($args) use ($_oJobIndexServiceCopy) {
                $_oJobIndexServiceCopy
                    ->isJobInIndex(Argument::exact("/remote/missing2"))
                    ->willReturn(false);
            })
            ->shouldBeCalled();

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(true)
            ->shouldBeCalledTimes(2);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($_oRemoteMissing1);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($_oRemoteMissing2);

        $this->oJobRepositoryRemote
            ->addJob(Argument::exact($_oRemoteMissing1))
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryRemote
            ->addJob(Argument::exact($_oRemoteMissing2))
            ->willReturn(true)
            ->shouldBeCalledTimes(1);


        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(2);

        $this->oLogger
            ->error(Argument::any())
            ->shouldNotBeCalled();

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingAppWithCircularDependencyFailure()
    {
        $_oRemoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $_oRemoteMissing1->dependencies = ["/remote/missing2"];
        $_oRemoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");
        $_oRemoteMissing2->dependencies = ["/remote/missing1"];

        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);


        $this->oJobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($_oRemoteMissing1);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($_oRemoteMissing2);

        $this->oLogger
            ->notice(Argument::any())
            ->shouldNotBeCalled();

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(2);

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingDepedencyNotAdded()
    {
        $_oRemoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $_oRemoteMissing1->dependencies = ["/remote/missing2"];

        $_oRemoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");

        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);


        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($_oRemoteMissing1);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($_oRemoteMissing2);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(false)
            ->shouldBeCalledTimes(2);  // will be called twice, once for dependency and once as its own entity

        $this->oJobIndexService
            ->removeJob("/remote/missing1")
            ->shouldNotBeCalled(); // root will not be removed. All dependencies however will vanish from index

        $this->oJobIndexService
            ->removeJob("/remote/missing2")
            ->shouldBeCalledTimes(1);



        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();

    }

    public function testStoreIndexedJobWithNonExistentDependencyApp()
    {
        $_oRemoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $_oRemoteMissing1->dependencies = ["/remote/missing2"];

        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($_oRemoteMissing1);


        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn(null);

        $this->oJobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->oLogger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }


    public function testStoreIndexedJobWithLocalMissingAppSuccess()
    {
        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn(["/local/missing1"]);


        $this->oJobIndexService
            ->isJobInIndex(Argument::exact("/local/missing1"))
            ->willReturn(true);

        $this->oJobIndexService
            ->removeJob(Argument::exact("/local/missing1"))
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryRemote
            ->removeJob("/local/missing1")
            ->willReturn(true)
            ->shouldBeCalled();

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);


        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobWithLocallyUpdatedAppSuccess()
    {
        $_oUpdatedApp = $this->getValidMarathonAppEntity("/local/update1");
        $this->oJobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->oJobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn(["/local/update1"]);

        $this->oJobIndexService
            ->isJobInIndex(Argument::exact("/local/update1"))
            ->willReturn(true);

        $this->oJobIndexService
            ->removeJob(Argument::exact("/local/update1"))
            ->shouldBeCalledTimes(1);

        $this->oJobRepositoryLocal
            ->getJob(Argument::exact("/local/update1"))
            ->willReturn($_oUpdatedApp)
            ->shouldBeCalled();

        $this->oJobRepositoryRemote
            ->updateJob(Argument::exact($_oUpdatedApp))
            ->willReturn(true)
            ->shouldBeCalled();

        $this->oLogger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $oMarathonStore = new MarathonStoreJobBusinessCase(
            $this->oJobIndexService->reveal(),
            $this->oJobRepositoryRemote->reveal(),
            $this->oJobRepositoryLocal->reveal(),
            $this->oJobComparisonBusinessCase->reveal(),
            $this->oLogger->reveal()
        );

        $oMarathonStore->storeIndexedJobs();
    }

}
