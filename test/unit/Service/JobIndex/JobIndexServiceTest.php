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
    private $cacheInterface;

    /** @var array  */
    private $testJobIndex = ['JobA' => 'JobA', 'JobB' => 'JobB'];

    public function setUp()
    {
        $this->cacheInterface = $this->prophesize('Chapi\Component\Cache\CacheInterface');

        $this->cacheInterface
            ->get(Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->testJobIndex)
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;
    }

    public function testAddJobSuccess()
    {
        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                array_merge($this->testJobIndex, ['JobC' => 'JobC'])
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $jobIndexService->addJob('JobC')
        );
    }

    public function testAddJobsSuccess()
    {
        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                array_merge($this->testJobIndex, ['JobC' => 'JobC', 'JobD' => 'JobD'])
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $jobIndexService->addJobs(['JobC', 'JobD'])
        );
    }

    public function testRemoveJobSuccess()
    {
        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                ['JobB' => 'JobB']
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $jobIndexService->removeJob('JobA')
        );
    }

    public function testRemoveJobsSuccess()
    {
        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                []
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $jobIndexService->removeJobs(['JobA', 'JobB'])
        );
    }

    public function testResetJobIndexSuccess()
    {
        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                $this->testJobIndex
            )
            ->shouldNotBeCalled()
        ;

        $this->cacheInterface
            ->set(
                Argument::exact(JobIndexService::JOB_INDEX_CACHE_KEY),
                []
            )
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertInstanceOf(
            'Chapi\Service\JobIndex\JobIndexServiceInterface',
            $jobIndexService->resetJobIndex()
        );
    }

    public function testGetJobIndexSuccess()
    {
        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertEquals(
            $this->testJobIndex,
            $jobIndexService->getJobIndex()
        );
    }

    public function testIsJobInIndexSuccess()
    {
        $jobIndexService = new JobIndexService($this->cacheInterface->reveal());

        $this->assertTrue(
            $jobIndexService->isJobInIndex('JobA')
        );
    }
}
