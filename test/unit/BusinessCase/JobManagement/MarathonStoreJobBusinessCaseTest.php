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

class MarathonStoreJobBusinessCaseTest extends \PHPUnit\Framework\TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobIndexService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryRemote;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobComparisonBusinessCase;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    protected function setUp(): void
    {
        $this->jobIndexService = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
        $this->jobRepositoryRemote = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobComparisonBusinessCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testStoreJobsToLocalRepositoryWithAddSuccess()
    {
        $remoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $localJobs = $this->createAppCollection(["/main/id1"]);

        $this->jobRepositoryRemote
            ->getJobs()
            ->shouldNotBeCalled();

        $this->jobRepositoryRemote
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($remoteJobs["/main/id1"]);

        $this->jobRepositoryRemote
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn($remoteJobs["/main/id2"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($localJobs["/main/id1"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn(null);

        $this->jobRepositoryLocal
            ->addJob(Argument::exact($remoteJobs["/main/id2"]))
            ->shouldBeCalled()
            ->willReturn(true);

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalled();

        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled();

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeJobsToLocalRepository(["/main/id1", "/main/id2"]);
    }

    public function testStoreJobsToLocalRepositoryWithAddFailure()
    {
        $remoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $localJobs = $this->createAppCollection(["/main/id1"]);

        $this->jobRepositoryRemote
            ->getJobs()
            ->shouldNotBeCalled();

        $this->jobRepositoryRemote
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($remoteJobs["/main/id1"]);

        $this->jobRepositoryRemote
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn($remoteJobs["/main/id2"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($localJobs["/main/id1"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn(null);

        $this->jobRepositoryLocal
            ->addJob($remoteJobs["/main/id2"])
            ->shouldBeCalled()
            ->willReturn(false);

        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalled();

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeJobsToLocalRepository(["/main/id1", "/main/id2"]);
    }

    public function testStoreJobsToLocalRepositoryWithUpdateSuccess()
    {
        $remoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $localJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $localJobs["/main/id2"]->cpus = 4;

        $this->jobRepositoryRemote
            ->getJobs()
            ->willReturn($remoteJobs);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($localJobs["/main/id1"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn($localJobs["/main/id2"]);


        $this->jobComparisonBusinessCase
        ->getJobDiff(Argument::exact($remoteJobs["/main/id1"]->getKey()))
        ->willReturn([])
        ->shouldBeCalledTimes(1);

        $this->jobComparisonBusinessCase
            ->getJobDiff(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediffs"])
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryLocal
            ->updateJob($remoteJobs["/main/id2"])
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $this->logger
            ->error(Argument::type('string'))
            ->shouldNotBeCalled();

        $this->jobIndexService
            ->removeJob(Argument::exact("/main/id2"))
            ->shouldBeCalledTimes(1);


        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeJobsToLocalRepository([], true);
    }

    public function testStoreJobsToLocalRepositoryWithRepositoryUpdateFailure()
    {
        $remoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $localJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $localJobs["/main/id2"]->cpus = 4;

        $this->jobRepositoryRemote
            ->getJobs()
            ->willReturn($remoteJobs);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($localJobs["/main/id1"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn($localJobs["/main/id2"]);


        $this->jobComparisonBusinessCase
        ->getJobDiff(Argument::exact($remoteJobs["/main/id1"]->getKey()))
        ->willReturn([])
        ->shouldBeCalledTimes(1);

        $this->jobComparisonBusinessCase
            ->getJobDiff(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediff"])
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryLocal
            ->updateJob($remoteJobs["/main/id2"])
            ->willReturn(false)
            ->shouldBeCalledTimes(1);

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldNotBeCalled();

        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $this->jobIndexService
            ->removeJob(Argument::exact("/main/id2"))
            ->shouldBeCalledTimes(1);


        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeJobsToLocalRepository([], true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStoreJobsToLocalRepositoryWithUpdateFailureWithoutForce()
    {
        $remoteJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);
        $localJobs = $this->createAppCollection(["/main/id1", "/main/id2"]);

        $localJobs["/main/id2"]->cpus = 4;

        $this->jobRepositoryRemote
            ->getJobs()
            ->willReturn($remoteJobs);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn($localJobs["/main/id1"]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn($localJobs["/main/id2"]);


        $this->jobComparisonBusinessCase
            ->getJobDiff(Argument::exact($remoteJobs["/main/id1"]->getKey()))
            ->willReturn([])
            ->shouldBeCalledTimes(1);

        $this->jobComparisonBusinessCase
            ->getJobDiff(Argument::exact($remoteJobs["/main/id2"]->getKey()))
            ->willReturn(["somediff"])
            ->shouldBeCalledTimes(1);


        $this->jobRepositoryLocal
            ->updateJob(Argument::any())
            ->willReturn(true)
            ->shouldNotBeCalled();

        $this->jobIndexService
            ->removeJob(Argument::any())
            ->shouldNotBeCalled();


        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeJobsToLocalRepository();
    }


    public function testStoreIndexedJobsWithRemoteMissingAppWithoutDependencySuccess()
    {
        $remoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");


        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true);

        $this->jobRepositoryLocal
            ->getJob("/remote/missing1")
            ->willReturn($remoteMissing1);

        $this->jobRepositoryRemote
            ->addJob($remoteMissing1)
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobIndexService
            ->removeJob(Argument::exact($remoteMissing1->getKey()))
            ->shouldBeCalledTimes(1);

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);


        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingAppWithDependencySuccess()
    {
        $remoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $remoteMissing1->dependencies = ["/remote/missing2"];
        $remoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");

        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);


        $jobIndexServiceCopy = $this->jobIndexService;
        $this->jobIndexService
            ->removeJob(Argument::exact($remoteMissing1->getKey()))
            ->will(function ($args) use ($jobIndexServiceCopy) {
                $jobIndexServiceCopy
                    ->isJobInIndex(Argument::exact("/remote/missing1"))
                    ->willReturn(false);
            })
            ->shouldBeCalled();

        $this->jobIndexService
            ->removeJob(Argument::exact($remoteMissing2->getKey()))
            ->will(function ($args) use ($jobIndexServiceCopy) {
                $jobIndexServiceCopy
                    ->isJobInIndex(Argument::exact("/remote/missing2"))
                    ->willReturn(false);
            })
            ->shouldBeCalled();

        $this->jobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(true)
            ->shouldBeCalledTimes(2);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($remoteMissing1);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($remoteMissing2);

        $this->jobRepositoryRemote
            ->addJob(Argument::exact($remoteMissing1))
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryRemote
            ->addJob(Argument::exact($remoteMissing2))
            ->willReturn(true)
            ->shouldBeCalledTimes(1);


        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(2);

        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled();

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingAppWithCircularDependencyFailure()
    {
        $remoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $remoteMissing1->dependencies = ["/remote/missing2"];
        $remoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");
        $remoteMissing2->dependencies = ["/remote/missing1"];

        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);


        $this->jobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($remoteMissing1);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($remoteMissing2);

        $this->logger
            ->notice(Argument::any())
            ->shouldNotBeCalled();

        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(2);

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobsWithRemoteMissingDepedencyNotAdded()
    {
        $remoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $remoteMissing1->dependencies = ["/remote/missing2"];

        $remoteMissing2 = $this->getValidMarathonAppEntity("/remote/missing2");

        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1", "/remote/missing2"]);


        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($remoteMissing1);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn($remoteMissing2);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing2")
            ->willReturn(false)
            ->shouldBeCalledTimes(2);  // will be called twice, once for dependency and once as its own entity

        $this->jobIndexService
            ->removeJob("/remote/missing1")
            ->shouldNotBeCalled(); // root will not be removed. All dependencies however will vanish from index

        $this->jobIndexService
            ->removeJob("/remote/missing2")
            ->shouldBeCalledTimes(1);



        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobWithNonExistentDependencyApp()
    {
        $remoteMissing1 = $this->getValidMarathonAppEntity("/remote/missing1");
        $remoteMissing1->dependencies = ["/remote/missing2"];

        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn(["/remote/missing1"]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing1"))
            ->willReturn($remoteMissing1);


        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/remote/missing2"))
            ->willReturn(null);

        $this->jobIndexService
            ->isJobInIndex("/remote/missing1")
            ->willReturn(true)
            ->shouldBeCalledTimes(1);

        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }


    public function testStoreIndexedJobWithLocalMissingAppSuccess()
    {
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn(["/local/missing1"]);


        $this->jobIndexService
            ->isJobInIndex(Argument::exact("/local/missing1"))
            ->willReturn(true);

        $this->jobIndexService
            ->removeJob(Argument::exact("/local/missing1"))
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryRemote
            ->removeJob("/local/missing1")
            ->willReturn(true)
            ->shouldBeCalled();

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);


        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }

    public function testStoreIndexedJobWithLocallyUpdatedAppSuccess()
    {
        $updatedApp = $this->getValidMarathonAppEntity("/local/update1");
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn([]);

        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn(["/local/update1"]);

        $this->jobIndexService
            ->isJobInIndex(Argument::exact("/local/update1"))
            ->willReturn(true);

        $this->jobIndexService
            ->removeJob(Argument::exact("/local/update1"))
            ->shouldBeCalledTimes(1);

        $this->jobRepositoryLocal
            ->getJob(Argument::exact("/local/update1"))
            ->willReturn($updatedApp)
            ->shouldBeCalled();

        $this->jobRepositoryRemote
            ->updateJob(Argument::exact($updatedApp))
            ->willReturn(true)
            ->shouldBeCalled();

        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalledTimes(1);

        $marathonStore = new MarathonStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->logger->reveal()
        );

        $marathonStore->storeIndexedJobs();
    }
}
