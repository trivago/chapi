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
use Chapi\Component\Chronos\ApiClientInterface;
use Chapi\Service\Chronos\JobStatsService;
use Prophecy\Argument;

class JobStatsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oApiClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    public function setUp()
    {
        $this->oApiClient = $this->prophesize('Chapi\Component\Chronos\ApiClientInterface');
        $this->oCache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
    }

    public function testCreateInstance()
    {
        $_oJobStatsService = new JobStatsService($this->oApiClient->reveal(), $this->oCache->reveal());
        $this->assertInstanceOf('Chapi\Service\Chronos\JobStatsServiceInterface', $_oJobStatsService);
    }

    public function testGetJobStatsSuccess()
    {
        $_sCacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $_aTestResult = [
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

        $this->oCache->get(Argument::exact($_sCacheKey))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oCache->set(Argument::exact($_sCacheKey), Argument::exact($_aTestResult), Argument::type('int'))->shouldBeCalledTimes(1);

        $this->oApiClient->getJobStats(Argument::exact('JobA'))->shouldBeCalledTimes(1)->willReturn($_aTestResult);

        $_oJobStatsService = new JobStatsService($this->oApiClient->reveal(), $this->oCache->reveal());
        $_oResult = $_oJobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $_oResult);
        $this->assertEquals($_aTestResult['histogram']['75thPercentile'], $_oResult->histogram->percentile75th);
        $this->assertEquals($_aTestResult['histogram']['95thPercentile'], $_oResult->histogram->percentile95th);
        $this->assertEquals($_aTestResult['histogram']['98thPercentile'], $_oResult->histogram->percentile98th);
        $this->assertEquals($_aTestResult['histogram']['99thPercentile'], $_oResult->histogram->percentile99th);
        $this->assertEquals($_aTestResult['histogram']['median'], $_oResult->histogram->median);
        $this->assertEquals($_aTestResult['histogram']['mean'], $_oResult->histogram->mean);
        $this->assertEquals($_aTestResult['histogram']['count'], $_oResult->histogram->count);
    }

    public function testGetJobStatsCacheSuccess()
    {
        $_sCacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $_aTestResult = [
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

        $this->oCache->get(Argument::exact($_sCacheKey))->shouldBeCalledTimes(1)->willReturn($_aTestResult);
        $this->oCache->set(Argument::exact($_sCacheKey), Argument::exact($_aTestResult), Argument::type('int'))->shouldNotBeCalled();

        $this->oApiClient->getJobStats(Argument::any())->shouldNotBeCalled();

        $_oJobStatsService = new JobStatsService($this->oApiClient->reveal(), $this->oCache->reveal());
        $_oResult = $_oJobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $_oResult);
        $this->assertEquals($_aTestResult['histogram']['75thPercentile'], $_oResult->histogram->percentile75th);
        $this->assertEquals($_aTestResult['histogram']['95thPercentile'], $_oResult->histogram->percentile95th);
        $this->assertEquals($_aTestResult['histogram']['98thPercentile'], $_oResult->histogram->percentile98th);
        $this->assertEquals($_aTestResult['histogram']['99thPercentile'], $_oResult->histogram->percentile99th);
        $this->assertEquals($_aTestResult['histogram']['median'], $_oResult->histogram->median);
        $this->assertEquals($_aTestResult['histogram']['mean'], $_oResult->histogram->mean);
        $this->assertEquals($_aTestResult['histogram']['count'], $_oResult->histogram->count);
    }

    public function testDoNotCacheEmptyResults()
    {
        $_sCacheKey = sprintf(JobStatsService::CACHE_KEY_JOB_STATS, 'JobA');
        $_aTestResult = [];

        $this->oCache->get(Argument::exact($_sCacheKey))->shouldBeCalledTimes(1)->willReturn(null);
        $this->oCache->set(Argument::exact($_sCacheKey), Argument::exact($_aTestResult), Argument::type('int'))->shouldNotBeCalled();

        $this->oApiClient->getJobStats(Argument::any())->shouldBeCalledTimes(1)->willReturn($_aTestResult);;

        $_oJobStatsService = new JobStatsService($this->oApiClient->reveal(), $this->oCache->reveal());
        $_oResult = $_oJobStatsService->getJobStats('JobA');

        $this->assertInstanceOf('Chapi\Entity\Chronos\JobStatsEntity', $_oResult);
        $this->assertEquals(0.0, $_oResult->histogram->percentile75th);
        $this->assertEquals(0, $_oResult->histogram->count);
    }
}