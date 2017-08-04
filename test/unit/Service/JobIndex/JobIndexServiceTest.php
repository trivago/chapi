<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-08
 */

namespace unit\Service\JobIndex;

use Chapi\Service\JobIndex\JobIndexService;
use Prophecy\Argument;

class JobIndexServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCacheInterface;

    /** @var array  */
    private $aTestJobIndex = ['JobA' => 'JobA', 'JobB' => 'JobB'];

    public function setUp()
    {
        $this->oCacheInterface = $this->prophesize('Chapi\Component\Cache\CacheInterface');

        $this->oCacheInterface
            ->get(Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->aTestJobIndex)
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;
    }

    public function testAddJobSuccess()
    {
        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                array_merge($this->aTestJobIndex, ['JobC' => 'JobC'])
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $_oJobIndexService->addJob('JobC')
        );
    }

    public function testAddJobsSuccess()
    {
        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                array_merge($this->aTestJobIndex, ['JobC' => 'JobC', 'JobD' => 'JobD'])
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $_oJobIndexService->addJobs(['JobC', 'JobD'])
        );
    }

    public function testRemoveJobSuccess()
    {
        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                ['JobB' => 'JobB']
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $_oJobIndexService->removeJob('JobA')
        );
    }

    public function testRemoveJobsSuccess()
    {
        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                []
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $_oJobIndexService->removeJobs(['JobA', 'JobB'])
        );
    }

    public function testResetJobIndexSuccess()
    {
        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->aTestJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->oCacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                []
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $_oJobIndexService->resetJobIndex()
        );
    }

    public function testGetJobIndexSuccess()
    {
        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertEquals(
            $this->aTestJobIndex,
            $_oJobIndexService->getJobIndex()
        );
    }

    public function testIsJobInIndexSuccess()
    {
        $_oJobIndexService = new JobIndexService($this->oCacheInterface->reveal());

        $this->assertTrue(
            $_oJobIndexService->isJobInIndex('JobA')
        );
    }
}
