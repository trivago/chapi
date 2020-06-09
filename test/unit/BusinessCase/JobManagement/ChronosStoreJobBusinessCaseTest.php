<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-23
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/18
 */


namespace unit\BusinessCase\JobManagement;

use Chapi\BusinessCase\JobManagement\ChronosStoreJobBusinessCase;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ChronosStoreJobBusinessCaseTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobIndexService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryRemote;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobComparisonBusinessCase;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobDependencyService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    protected function setUp(): void
    {
        $this->jobIndexService = $this->prophesize('Chapi\Service\JobIndex\JobIndexServiceInterface');
        $this->jobRepositoryRemote = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobComparisonBusinessCase = $this->prophesize('Chapi\BusinessCase\Comparison\JobComparisonInterface');
        $this->jobDependencyService = $this->prophesize('Chapi\Service\JobDependencies\JobDependencyServiceInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    private function setUpJobsToAdd(array $missingJobs, $isInIndex = true)
    {
        // general mocking
        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalled()
        ;

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        foreach ($missingJobs as $jobName) {
            $jobEntity = $this->getValidScheduledJobEntity($jobName);

            $this->jobRepositoryLocal
                ->getJob(Argument::exact($jobName))
                ->willReturn($jobEntity)
                ->shouldBeCalledTimes(1)
            ;

            $this->jobIndexService
                ->isJobInIndex(Argument::exact($jobName))
                ->willReturn($isInIndex)
                ->shouldBeCalledTimes(1)
            ;

            if ($isInIndex) {
                $this->jobRepositoryRemote
                    ->addJob(Argument::exact($jobEntity))
                    ->willReturn(true)
                    ->shouldBeCalledTimes(1)
                ;

                $this->jobIndexService
                    ->removeJob(Argument::exact($jobName))
                    ->shouldBeCalledTimes(1)
                ;
            }
        }
    }

    private function setUpJobsToRemove(array $localMissingJobs, $isInIndex = true)
    {
        // general mocking
        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalled()
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        foreach ($localMissingJobs as $jobName) {
            $this->jobIndexService
                ->isJobInIndex(Argument::exact($jobName))
                ->willReturn($isInIndex)
                ->shouldBeCalledTimes(1)
            ;

            if ($isInIndex) {
                $this->jobRepositoryRemote
                    ->removeJob(Argument::exact($jobName))
                    ->willReturn(true)
                    ->shouldBeCalledTimes(1)
                ;

                $this->jobIndexService
                    ->removeJob(Argument::exact($jobName))
                    ->shouldBeCalledTimes(1)
                ;
            }
        }
    }

    private function setUpJobsToUpdate(array $localJobUpdates, $isInIndex = true, $hasSameJobType = true)
    {
        // general mocking
        $this->logger
            ->notice(Argument::type('string'))
            ->shouldBeCalled()
        ;

        // get jobs to update
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        foreach ($localJobUpdates as $jobName) {
            $jobEntity = $this->getValidScheduledJobEntity($jobName);

            $this->jobRepositoryLocal
                ->getJob(Argument::exact($jobName))
                ->willReturn($jobEntity)
                ->shouldBeCalledTimes(1)
            ;

            $this->jobIndexService
                ->isJobInIndex(Argument::exact($jobName))
                ->willReturn($isInIndex)
                ->shouldBeCalledTimes(1)
            ;

            if ($isInIndex) {
                $this->jobRepositoryRemote
                    ->getJob(Argument::exact($jobName))
                    ->willReturn($jobEntity)
                    ->shouldBeCalledTimes(1)
                ;

                $this->jobComparisonBusinessCase
                    ->hasSameJobType(Argument::exact($jobEntity), Argument::exact($jobEntity))
                    ->willReturn($hasSameJobType)
                    ->shouldBeCalledTimes(1)
                ;


                if ($hasSameJobType) {
                    $this->jobRepositoryRemote
                        ->updateJob(Argument::exact($jobEntity))
                        ->willReturn(true)
                        ->shouldBeCalledTimes(1)
                    ;
                } else {
                    $this->jobRepositoryRemote
                        ->removeJob(Argument::exact($jobEntity->name))
                        ->willReturn(true)
                        ->shouldBeCalledTimes(1)
                    ;

                    $this->jobRepositoryRemote
                        ->addJob(Argument::exact($jobEntity))
                        ->willReturn(true)
                        ->shouldBeCalledTimes(1)
                    ;
                }



                $this->jobIndexService
                    ->removeJob(Argument::exact($jobName))
                    ->shouldBeCalledTimes(1)
                ;
            }
        }
    }

    public function testAddingJobsByStoreIndexedJobsSuccess()
    {
        $jobNameA = 'JobA';
        $jobNameB = 'JobB';

        $missingJobs = [$jobNameA, $jobNameB];

        // general mocking
        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;


        $this->setUpJobsToAdd($missingJobs);
        $this->setUpJobsToRemove([]);
        $this->setUpJobsToUpdate([]);

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testRemovingJobsByStoreIndexedJobsSuccess()
    {
        $localMissingJobs = ['JobA', 'JobB'];

        // general mocking
        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->setUpJobsToAdd([]);
        $this->setUpJobsToRemove($localMissingJobs);
        $this->setUpJobsToUpdate([]);

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testUpdatingJobsByStoreIndexedJobsSuccess()
    {
        $localJobUpdates = ['JobA', 'JobB'];

        // general mocking
        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->setUpJobsToAdd([]);
        $this->setUpJobsToRemove([]);
        $this->setUpJobsToUpdate($localJobUpdates);

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testUpdatingJobsByStoreIndexedJobsWithDifferentTypesSuccess()
    {
        $localJobUpdates = ['JobA', 'JobB'];

        // general mocking
        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->setUpJobsToAdd([]);
        $this->setUpJobsToRemove([]);
        $this->setUpJobsToUpdate($localJobUpdates, true, false);

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsTogetherSuccess()
    {
        $this->setUpJobsToAdd(['JobA', 'JobB']);
        $this->setUpJobsToRemove(['JobC']);
        $this->setUpJobsToUpdate(['JobD']);

        // general mocking
        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsTogetherWithNoIndexSuccess()
    {
        $this->setUpJobsToAdd(['JobA', 'JobB'], false);
        $this->setUpJobsToRemove(['JobC'], false);
        $this->setUpJobsToUpdate(['JobD'], false);

        // general mocking
        $this->logger
            ->notice(Argument::type('string'))
            ->shouldNotBeCalled()
        ;

        $this->logger
            ->error(Argument::any())
            ->shouldNotBeCalled()
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsFailureInAdding()
    {
        $jobNameA = 'JobA';
        $jobNameB = 'JobB';

        $missingJobs = [$jobNameA, $jobNameB];
        $localMissingJobs = [];
        $localJobUpdates = [];

        $jobEntityA = $this->getValidScheduledJobEntity($jobNameA);
        $jobEntityB = $this->getValidScheduledJobEntity($jobNameB);

        // general mocking
        $this->logger
            ->notice(Argument::type('string'))
            ->shouldNotBeCalled()
        ;

        $this->logger
            ->error(Argument::type('string'))
            ->shouldBeCalledTimes(1)
        ;

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($jobNameA))
            ->willReturn($jobEntityA)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact($jobNameA))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact($jobNameB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryLocal
            ->getJob(Argument::exact($jobNameB))
            ->willReturn($jobEntityB)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->addJob(Argument::exact($jobEntityB))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->removeJob(Argument::exact($jobNameB))
            ->shouldNotBeCalled()
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreJobsToLocalRepositorySuccess()
    {
        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = clone $jobEntityA1;
        $jobEntityA2->disabled = true;

        $jobEntityB1 = $this->getValidDependencyJobEntity('JobB', 'JobC');

        $this->jobRepositoryRemote->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntityA1);
        $this->jobRepositoryRemote->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn($jobEntityB1);

        $this->jobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntityA2);
        $this->jobRepositoryLocal->getJob(Argument::exact('JobB'))->shouldBeCalledTimes(1)->willReturn(null);

        $this->jobRepositoryLocal->addJob(Argument::exact($jobEntityB1))->shouldBeCalledTimes(1)->willReturn(true);

        $this->jobComparisonBusinessCase->getJobDiff(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn(['disabled'=>'div string']);

        $this->jobRepositoryLocal->updateJob(Argument::exact($jobEntityA1))->shouldBeCalledTimes(1)->willReturn(true);

        $this->jobIndexService->removeJob(Argument::exact('JobA'))->shouldBeCalledTimes(1);

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeJobsToLocalRepository(['JobA', 'JobB'], true));

        // spy
        $this->logger->error(Argument::type('string'))->shouldNotBeCalled();
        $this->logger->notice(Argument::type('string'))->shouldBeCalled();
    }

    public function testStoreJobsToLocalRepositoryFailureBecauseJobExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $jobEntityA1 = $this->getValidScheduledJobEntity('JobA');
        $jobEntityA2 = clone $jobEntityA1;
        $jobEntityA2->disabled = true;


        $this->jobRepositoryRemote->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntityA1);
        $this->jobRepositoryLocal->getJob(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($jobEntityA2);

        $this->jobComparisonBusinessCase->getJobDiff(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn(['disabled'=>'div string']);

        $this->jobRepositoryLocal->updateJob(Argument::exact($jobEntityA1))->shouldNotBeCalled();

        $this->jobIndexService->removeJob(Argument::exact('JobA'))->shouldNotBeCalled();

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeJobsToLocalRepository(['JobA']));

        // spy
        $this->logger->error(Argument::type('string'))->shouldBeCalled();
        $this->logger->notice(Argument::type('string'))->shouldNotBeCalled();
    }

    public function testStoreIndexedJobsSuccessWithParentJob()
    {
        $missingJobs = ['JobA'];
        $localMissingJobs = [];
        $localJobUpdates = [];

        $jobEntityA = $this->getValidDependencyJobEntity();

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact('JobA'))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryLocal
            ->getJob(Argument::exact('JobA'))
            ->willReturn($jobEntityA)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->hasJob(Argument::exact('JobB'))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->addJob(Argument::exact($jobEntityA))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->jobIndexService
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsFailForMissingParentJob()
    {
        $missingJobs = ['JobA'];
        $localMissingJobs = [];
        $localJobUpdates = [];

        $jobEntityA = $this->getValidDependencyJobEntity();

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact('JobA'))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryLocal
            ->getJob(Argument::exact('JobA'))
            ->willReturn($jobEntityA)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->hasJob(Argument::exact('JobB'))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->addJob(Argument::exact($jobEntityA))
            ->shouldNotBeCalled()
        ;

        $this->jobIndexService
            ->removeJob(Argument::exact('JobA'))
            ->shouldNotBeCalled()
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsSuccessDeleteWithParentJob()
    {
        $missingJobs = [];
        $localMissingJobs = ['JobA'];
        $localJobUpdates = [];

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact('JobA'))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobDependencyService
            ->getChildJobs(Argument::exact('JobA'), JobDependencyServiceInterface::REPOSITORY_CHRONOS)
            ->willReturn([])
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->jobIndexService
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }

    public function testStoreIndexedJobsFailureDeleteWithParentJob()
    {
        $missingJobs = [];
        $localMissingJobs = ['JobA'];
        $localJobUpdates = [];

        // add new jobs to chronos
        $this->jobComparisonBusinessCase
            ->getRemoteMissingJobs()
            ->willReturn($missingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // delete missing jobs from chronos
        $this->jobComparisonBusinessCase
            ->getLocalMissingJobs()
            ->willReturn($localMissingJobs)
            ->shouldBeCalledTimes(1)
        ;

        // update jobs on chronos
        $this->jobComparisonBusinessCase
            ->getLocalJobUpdates()
            ->willReturn($localJobUpdates)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact('JobA'))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobIndexService
            ->isJobInIndex(Argument::exact('JobB'))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->jobDependencyService
            ->getChildJobs(Argument::exact('JobA'), JobDependencyServiceInterface::REPOSITORY_CHRONOS)
            ->willReturn(['JobB'])
            ->shouldBeCalledTimes(1)
        ;

        $this->jobRepositoryRemote
            ->removeJob(Argument::exact('JobA'))
            ->shouldNotBeCalled()
        ;

        $this->jobIndexService
            ->removeJob(Argument::exact('JobA'))
            ->shouldNotBeCalled()
        ;

        // test
        $storeJobBusinessCase = new ChronosStoreJobBusinessCase(
            $this->jobIndexService->reveal(),
            $this->jobRepositoryRemote->reveal(),
            $this->jobRepositoryLocal->reveal(),
            $this->jobComparisonBusinessCase->reveal(),
            $this->jobDependencyService->reveal(),
            $this->logger->reveal()
        );

        $this->assertNull($storeJobBusinessCase->storeIndexedJobs());
    }
}
