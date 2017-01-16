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
    private $oRepositoryBridge;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oEntityFilter;

    public function setUp()
    {
        $this->oRepositoryBridge = $this->prophesize('Chapi\Service\JobRepository\BridgeInterface');
        $this->oEntityFilter = $this->prophesize('Chapi\Service\JobRepository\JobFilterInterface');
    }

    public function testGetJobSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');
        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([
                $_oEntity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $_oJobEntity = $_oJobRepository->getJob('JobA');

        // known job
        $this->assertInstanceOf(
            'Chapi\Entity\JobEntityInterface',
            $_oJobEntity
        );

        $this->assertEquals(
            'JobA',
            $_oJobEntity->name
        );

        // empty job
        $_oJobEntity = $_oJobRepository->getJob('JobZ');

        $this->assertNull($_oJobEntity, "Expected null for non existing job");
    }

    public function testGetJobSuccessWithFilterFailure()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');
        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([
                $_oEntity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(false);


        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $_oJobEntity = $_oJobRepository->getJob('JobA');

        $this->assertNull($_oJobEntity, "Expected null for non-interesting job");

    }

    public function testGetJobsSuccessWithFilterSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');
        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([
                $_oEntity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $_oJobCollection = $_oJobRepository->getJobs();

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\JobCollection',
            $_oJobCollection
        );

        $this->assertInstanceOf(
            'Chapi\Entity\JobEntityInterface',
            $_oJobCollection['JobA']
        );
    }

    public function testGetJobsSuccessWithFilterFailure()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');
        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([
                $_oEntity
            ])
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(false);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $_oJobCollection = $_oJobRepository->getJobs();

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\JobCollection',
            $_oJobCollection
        );

        $this->assertEmpty($_oJobCollection, "Expected empty job collection with no interestesting jobs");
    }


    public function testAddJobSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');

        $this->oRepositoryBridge
            ->addJob(Argument::exact($_oEntity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertTrue(
            $_oJobRepository->addJob($_oEntity)
        );
    }

    public function testAddJobSuccessWithInitialisedJobCollection()
    {
        $_oEntityA = $this->getValidScheduledJobEntity('JobA');
        $_oEntityB = $this->getValidScheduledJobEntity('JobB');

        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([$_oEntityA])
            ->shouldBeCalledTimes(1)
        ;

        $this->oRepositoryBridge
            ->addJob(Argument::exact($_oEntityB))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntityA))
            ->willReturn(true);


        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $_oJobEntityResult = $_oJobRepository->getJob('JobA');

        // known job
        $this->assertEquals(
            'JobA',
            $_oJobEntityResult->name
        );

        $this->assertTrue(
            $_oJobRepository->addJob($_oEntityB)
        );
    }

    public function testAddJobFailure()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');

        $this->oRepositoryBridge
            ->addJob(Argument::exact($_oEntity))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertFalse(
            $_oJobRepository->addJob($_oEntity)
        );
    }

    public function testUpdateJobSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');

        $this->oRepositoryBridge
            ->updateJob(Argument::exact($_oEntity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertTrue(
            $_oJobRepository->updateJob($_oEntity)
        );
    }

    public function testRemoveJobSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');

        $this->oRepositoryBridge
            ->removeJob(Argument::exact($_oEntity))
            ->willReturn(true)
            ->shouldBeCalledTimes(1)
        ;

        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([$_oEntity])
            ->shouldBeCalledTimes(1)
        ;

        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertTrue(
            $_oJobRepository->removeJob($_oEntity->name)
        );

        $this->assertNull(
            $_oJobRepository->getJob('JobA')
        );
    }

    public function testRemoveJobFailure()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');

        $this->oRepositoryBridge
            ->removeJob(Argument::exact($_oEntity))
            ->willReturn(false)
            ->shouldBeCalledTimes(1)
        ;

        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([$_oEntity])
            ->shouldBeCalledTimes(1)
        ;


        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertFalse(
            $_oJobRepository->removeJob($_oEntity->name)
        );

        $this->assertEquals(
            $_oEntity,
            $_oJobRepository->getJob('JobA')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRemoveJobFailureException()
    {
        $this->oRepositoryBridge
            ->removeJob(Argument::any())
            ->shouldNotBeCalled()
        ;

        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([])
            ->shouldBeCalledTimes(1)
        ;


        $this->oEntityFilter
            ->isInteresting(Argument::any())
            ->willReturn(true)
            ->shouldBeCalledTimes(0);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertFalse(
            $_oJobRepository->removeJob('JobA')
        );
    }

    public function testHasJobSuccess()
    {
        $_oEntity = $this->getValidScheduledJobEntity('JobA');
        $this->oRepositoryBridge
            ->getJobs()
            ->willReturn([
                $_oEntity
            ])
            ->shouldBeCalledTimes(1)
        ;


        $this->oEntityFilter
            ->isInteresting(Argument::exact($_oEntity))
            ->willReturn(true);

        $_oJobRepository = new JobRepository(
            $this->oRepositoryBridge->reveal(),
            $this->oEntityFilter->reveal()
        );

        $this->assertTrue($_oJobRepository->hasJob('JobA'));
        $this->assertFalse($_oJobRepository->hasJob('JobB'));
    }
}