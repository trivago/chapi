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

class JobDependencyServiceTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryLocal;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobRepositoryChronos;

    public function setUp()
    {
        $this->oJobRepositoryChronos = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
        $this->oJobRepositoryLocal = $this->prophesize('Chapi\Service\JobRepository\JobRepositoryInterface');
    }

    public function testCreateInstance()
    {
        $_oJobDependencyService = new JobDependencyService($this->oJobRepositoryLocal->reveal(), $this->oJobRepositoryChronos->reveal());
        $this->assertInstanceOf('Chapi\Service\JobDependencies\JobDependencyServiceInterface', $_oJobDependencyService);
    }

    public function testGetChildJobsLocal()
    {
        $this->oJobRepositoryLocal->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->oJobRepositoryChronos->getJobs()->shouldNotBeCalled();

        $_oJobDependencyService = new JobDependencyService($this->oJobRepositoryLocal->reveal(), $this->oJobRepositoryChronos->reveal());
        $_aResult = $_oJobDependencyService->getChildJobs('JobA', JobDependencyService::REPOSITORY_LOCAL);

        $this->assertTrue(is_array($_aResult));
        $this->assertTrue(in_array('JobB', $_aResult));

        $this->assertTrue($_oJobDependencyService->hasChildJobs('JobA', JobDependencyService::REPOSITORY_LOCAL));
        $this->assertFalse($_oJobDependencyService->hasChildJobs('JobB', JobDependencyService::REPOSITORY_LOCAL));
    }

    public function testGetChildJobsChronos()
    {
        $this->oJobRepositoryChronos->getJobs()->shouldBeCalledTimes(1)->willReturn($this->createJobCollection());
        $this->oJobRepositoryLocal->getJobs()->shouldNotBeCalled();

        $_oJobDependencyService = new JobDependencyService($this->oJobRepositoryLocal->reveal(), $this->oJobRepositoryChronos->reveal());
        $_aResult = $_oJobDependencyService->getChildJobs('JobA', JobDependencyService::REPOSITORY_CHRONOS);

        $this->assertTrue(is_array($_aResult));
        $this->assertTrue(in_array('JobB', $_aResult));

        $this->assertTrue($_oJobDependencyService->hasChildJobs('JobA', JobDependencyService::REPOSITORY_CHRONOS));
        $this->assertFalse($_oJobDependencyService->hasChildJobs('JobB', JobDependencyService::REPOSITORY_CHRONOS));
    }
}
