<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-12
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */


namespace unit\Service\JobDependency;

use Chapi\Service\JobDependencies\JobDependencyService;
use ChapiTest\src\TestTraits\JobEntityTrait;

class JobDependencyServiceTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobRepositoryChronos;

    protected function setUp(): void
    {
        $this->jobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->jobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
    }

    public function testCreateInstance()
    {
        $jobDependencyService = new JobDependencyService($this->jobRepositoryLocal->reveal(), $this->jobRepositoryChronos->reveal());
        $this->assertInstanceOf('Chapi\Service\JobDependencies\JobDependencyServiceInterface', $jobDependencyService);
    }

    public function testGetChildJobsLocal()
    {
        $this->jobRepositoryLocal->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->jobRepositoryChronos->getJobs()->shouldNotBeCalled();

        $jobDependencyService = new JobDependencyService($this->jobRepositoryLocal->reveal(), $this->jobRepositoryChronos->reveal());
        $result = $jobDependencyService->getChildJobs('JobA', JobDependencyService::REPOSITORY_LOCAL);

        $this->assertIsArray($result);
        $this->assertContains('JobB', $result);

        $this->assertTrue($jobDependencyService->hasChildJobs('JobA', JobDependencyService::REPOSITORY_LOCAL));
        $this->assertFalse($jobDependencyService->hasChildJobs('JobB', JobDependencyService::REPOSITORY_LOCAL));
    }

    public function testGetChildJobsChronos()
    {
        $this->jobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->jobRepositoryLocal->getJobs()->shouldNotBeCalled();

        $jobDependencyService = new JobDependencyService($this->jobRepositoryLocal->reveal(), $this->jobRepositoryChronos->reveal());
        $result = $jobDependencyService->getChildJobs('JobA', JobDependencyService::REPOSITORY_CHRONOS);

        $this->assertIsArray($result);
        $this->assertContains('JobB', $result);

        $this->assertTrue($jobDependencyService->hasChildJobs('JobA', JobDependencyService::REPOSITORY_CHRONOS));
        $this->assertFalse($jobDependencyService->hasChildJobs('JobB', JobDependencyService::REPOSITORY_CHRONOS));
    }
}
