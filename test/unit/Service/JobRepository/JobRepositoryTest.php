<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 *
 */


namespace unit\Service\JobRepository;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobRepository\JobRepository;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class JobRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $repositoryBridge;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $entityFilter;

    public function setUp()
    {
        $this->repositoryBridge = $this->prophesize('Chapi\Service\JobRepository\BridgeInterface');
        $this->entityFilter = $this->prophesize('Chapi\Service\JobRepository\Filter\JobFilterInterface');
    }

    public function testGetJobSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');
        $this->repositoryBridge
            ->getJobs()
            ->willReturn([
                $entity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $jobEntity = $jobRepository->getJob('JobA');

        // known job
        $this->assertInstanceOf(
            'Chapi\Entity\JobEntityInterface',
            $jobEntity
        );

        $this->assertEquals(
            'JobA',
            $jobEntity->name
        );

        // empty job
        $jobEntity = $jobRepository->getJob('JobZ');

        $this->assertNull($jobEntity, "Expected null for non existing job");
    }

    public function testGetJobSuccessWithFilterFailure()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');
        $this->repositoryBridge
            ->getJobs()
            ->willReturn([
                $entity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(false);


        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $jobEntity = $jobRepository->getJob('JobA');

        $this->assertNull($jobEntity, "Expected null for non-interesting job");
    }

    public function testGetJobsSuccessWithFilterSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');
        $this->repositoryBridge
            ->getJobs()
            ->willReturn([
                $entity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $jobCollection = $jobRepository->getJobs();

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\JobCollection',
            $jobCollection
        );

        $this->assertInstanceOf(
            'Chapi\Entity\JobEntityInterface',
            $jobCollection['JobA']
        );
    }

    public function testGetJobsSuccessWithFilterFailure()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');
        $this->repositoryBridge
            ->getJobs()
            ->willReturn([
                $entity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(false);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $jobCollection = $jobRepository->getJobs();

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\JobCollection',
            $jobCollection
        );

        $this->assertEmpty($jobCollection, "Expected empty job collection with no interestesting jobs");
    }


    public function testAddJobSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');

        $this->repositoryBridge
            ->addJob(Argument::exact($entity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertTrue(
            $jobRepository->addJob($entity)
        );
    }

    public function testAddJobSuccessWithInitialisedJobCollection()
    {
        $entityA = $this->getValidScheduledJobEntity('JobA');
        $entityB = $this->getValidScheduledJobEntity('JobB');

        $this->repositoryBridge
            ->getJobs()
            ->willReturn([$entityA])
            ->shouldBeCalledTimes(1)
        ;

        $this->repositoryBridge
            ->addJob(Argument::exact($entityB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entityA))
            ->willReturn(true);


        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $jobEntityResult = $jobRepository->getJob('JobA');

        // known job
        $this->assertEquals(
            'JobA',
            $jobEntityResult->name
        );

        $this->assertTrue(
            $jobRepository->addJob($entityB)
        );
    }

    public function testAddJobFailure()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');

        $this->repositoryBridge
            ->addJob(Argument::exact($entity))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertFalse(
            $jobRepository->addJob($entity)
        );
    }

    public function testUpdateJobSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');

        $this->repositoryBridge
            ->updateJob(Argument::exact($entity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertTrue(
            $jobRepository->updateJob($entity)
        );
    }

    public function testRemoveJobSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');

        $this->repositoryBridge
            ->removeJob(Argument::exact($entity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->repositoryBridge
            ->getJobs()
            ->willReturn([$entity])
            ->shouldBeCalledTimes(1)
        ;

        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertTrue(
            $jobRepository->removeJob($entity->name)
        );

        $this->assertNull(
            $jobRepository->getJob('JobA')
        );
    }

    public function testRemoveJobFailure()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');

        $this->repositoryBridge
            ->removeJob(Argument::exact($entity))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->repositoryBridge
            ->getJobs()
            ->willReturn([$entity])
            ->shouldBeCalledTimes(1)
        ;


        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertFalse(
            $jobRepository->removeJob($entity->name)
        );

        $this->assertEquals(
            $entity,
            $jobRepository->getJob('JobA')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveJobFailureException()
    {
        $this->repositoryBridge
            ->removeJob(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->repositoryBridge
            ->getJobs()
            ->willReturn([])
            ->shouldBeCalledTimes(1)
        ;


        $this->entityFilter
            ->isInteresting(Argument::any())
            ->willReturn(true)
            ->shouldBeCalledTimes(0);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertFalse(
            $jobRepository->removeJob('JobA')
        );
    }

    public function testHasJobSuccess()
    {
        $entity = $this->getValidScheduledJobEntity('JobA');
        $this->repositoryBridge
            ->getJobs()
            ->willReturn([
                $entity
            ])
            ->shouldBeCalledTimes(1)
        ;


        $this->entityFilter
            ->isInteresting(Argument::exact($entity))
            ->willReturn(true);

        $jobRepository = new JobRepository(
            $this->repositoryBridge->reveal(),
            $this->entityFilter->reveal()
        );

        $this->assertTrue($jobRepository->hasJob('JobA'));
        $this->assertFalse($jobRepository->hasJob('JobB'));
    }
}
