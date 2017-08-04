<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-10
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace unit\Service\Chronos;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Service\Chronos\JobStatsService;
use Prophecy\Argument;

class JobStatsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $apiClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $cache;

    public function setUp()
    {
        $this->apiClient = $this->prophesize('Chapi\Component\RemoteClients\ApiClientInterface');
        $this->cache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
    }

    public function testCreateInstance()
    {
        $jobStatsService = new JobStatsService($this->apiClient->reveal(), $this->cache->reveal());
        $this->assertInstanceOf('Chapi\Service\Chronos\JobStatsServiceInterface', $jobStatsService);
    }

    public function testGetJobStatsSuccess()
    {
        $cacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $testResult = [
            'histogram' => [
                '75thPercentile' => 1.34,
                '95thPercentile' => 2.23,
                '98thPercentile' => 3.23,
                '99thPercentile' => 4.23,
                'median' => 11.11,
                'mean' => 2.22,
                'count' => 10
            ],
            'taskStatHistory' => []
        ];

        $this->cache->get(Argument::exact($cacheKey))->shouldBeCalledTimes(1)->willReturn(null);
        $this->cache->set(Argument::exact($cacheKey), Argument::exact($testResult), Argument::type('int'))->shouldBeCalledTimes(1);

        $this->apiClient->getJobStats(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($testResult);

        $jobStatsService = new JobStatsService($this->apiClient->reveal(), $this->cache->reveal());
        $result = $jobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $result);
        $this->assertEquals($testResult['histogram']['75thPercentile'], $result->histogram->percentile75th);
        $this->assertEquals($testResult['histogram']['95thPercentile'], $result->histogram->percentile95th);
        $this->assertEquals($testResult['histogram']['98thPercentile'], $result->histogram->percentile98th);
        $this->assertEquals($testResult['histogram']['99thPercentile'], $result->histogram->percentile99th);
        $this->assertEquals($testResult['histogram']['median'], $result->histogram->median);
        $this->assertEquals($testResult['histogram']['mean'], $result->histogram->mean);
        $this->assertEquals($testResult['histogram']['count'], $result->histogram->count);
    }

    public function testGetJobStatsCacheSuccess()
    {
        $cacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $testResult = [
            'histogram' => [
                '75thPercentile' => 1.34,
                '95thPercentile' => 2.23,
                '98thPercentile' => 3.23,
                '99thPercentile' => 4.23,
                'median' => 11.11,
                'mean' => 2.22,
                'count' => 10
            ],
            'taskStatHistory' => []
        ];

        $this->cache->get(Argument::exact($cacheKey))->shouldBeCalledTimes(1)->willReturn($testResult);
        $this->cache->set(Argument::exact($cacheKey), Argument::exact($testResult), Argument::type('int'))->shouldNotBeCalled();

        $this->apiClient->getJobStats(Argument::any())->shouldNotBeCalled();

        $jobStatsService = new JobStatsService($this->apiClient->reveal(), $this->cache->reveal());
        $result = $jobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $result);
        $this->assertEquals($testResult['histogram']['75thPercentile'], $result->histogram->percentile75th);
        $this->assertEquals($testResult['histogram']['95thPercentile'], $result->histogram->percentile95th);
        $this->assertEquals($testResult['histogram']['98thPercentile'], $result->histogram->percentile98th);
        $this->assertEquals($testResult['histogram']['99thPercentile'], $result->histogram->percentile99th);
        $this->assertEquals($testResult['histogram']['median'], $result->histogram->median);
        $this->assertEquals($testResult['histogram']['mean'], $result->histogram->mean);
        $this->assertEquals($testResult['histogram']['count'], $result->histogram->count);
    }

    public function testDoNotCacheEmptyResults()
    {
        $cacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $testResult = [];

        $this->cache->get(Argument::exact($cacheKey))->shouldBeCalledTimes(1)->willReturn(null);
        $this->cache->set(Argument::exact($cacheKey), Argument::exact($testResult), Argument::type('int'))->shouldNotBeCalled();

        $this->apiClient->getJobStats(Argument::any())->shouldBeCalledTimes(1)->willReturn($testResult);
        ;

        $jobStatsService = new JobStatsService($this->apiClient->reveal(), $this->cache->reveal());
        $result = $jobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $result);
        $this->assertEquals(0.0, $result->histogram->percentile75th);
        $this->assertEquals(0, $result->histogram->count);
    }
}
