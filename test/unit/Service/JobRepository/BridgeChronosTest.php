<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-10
 *
 */


namespace unit\Service\JobRepository;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobRepository\BridgeChronos;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class BridgeChronosTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $apiClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $cache;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $jobEntityValidatorService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    private $jsonListingJobs = '[{"name":"job-a","command":"echo job","shell":true,"epsilon":"PT1M","executor":"","executorFlags":"","retries":0,"owner":"mail@address.com","ownerName":"Foo","description":"Generates something","async":false,"successCount":10918,"errorCount":16,"lastSuccess":"2015-08-10T07:35:04.529Z","lastError":"2015-08-03T12:15:06.183Z","cpus":0.1,"disk":24.0,"mem":128.0,"disabled":false,"softError":false,"dataProcessingJobType":false,"errorsSinceLastSuccess":0,"uris":[],"environmentVariables":[],"arguments":[],"highPriority":false,"runAsUser":"root","schedule":"R/2015-08-10T09:40:00.000+02:00/PT5M","scheduleTimeZone":"Europe/Berlin"},{"name":"job-b","command":"echo jobb","shell":true,"epsilon":"PT60S","executor":"","executorFlags":"","retries":0,"owner":"mail@address.com","ownerName":"Bar","description":"Do another thing","async":false,"successCount":1145,"errorCount":3,"lastSuccess":"2015-08-10T07:15:11.275Z","lastError":"2015-08-03T12:15:06.146Z","cpus":0.1,"disk":24.0,"mem":64.0,"disabled":false,"softError":false,"dataProcessingJobType":false,"errorsSinceLastSuccess":0,"uris":[],"environmentVariables":[],"arguments":[],"highPriority":false,"runAsUser":"root","schedule":"R/2015-08-10T09:45:00.000+02:00/PT30M","scheduleTimeZone":"Europe/Berlin"}]';

    private $listingJobs = [];

    public function setUp()
    {
        $this->listingJobs = json_decode($this->jsonListingJobs, true);

        $this->apiClient = $this->prophesize('Chapi\Component\RemoteClients\ApiClientInterface');
        $this->apiClient
            ->listingJobs()
            ->willReturn($this->listingJobs)
        ;

        $this->cache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
        $this->cache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->willReturn(null)
        ;

        $this->cache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->listingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->willReturn(true)
        ;

        $this->jobEntityValidatorService = $this->prophesize('Chapi\Service\JobValidator\ChronosJobValidatorService');

        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testGetJobsSuccess()
    {
        $this->apiClient
            ->listingJobs()
            ->shouldBeCalledTimes(1)
        ;

        $this->cache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
        ;

        $this->cache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->listingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->shouldBeCalledTimes(1)
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $jobs = $jobRepositoryChronos->getJobs();

        $this->assertInternalType(
            'array',
            $jobs
        );

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\ChronosJobEntity',
            $jobs[0]
        );

        $i = 0;
        foreach ($jobs as $jobName => $jobEntity) {
            $this->assertEquals(
                $this->listingJobs[$i]['name'],
                $jobEntity->name
            );

            ++$i;
        }
    }

    public function testGetJobsWithCacheSuccess()
    {
        $this->apiClient
            ->listingJobs()
            ->shouldNotBeCalled()
        ;

        $this->cache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->listingJobs)
        ;

        $this->cache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->listingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->shouldNotBeCalled()
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $jobs = $jobRepositoryChronos->getJobs();

        $this->assertInternalType(
            'array',
            $jobs
        );

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\ChronosJobEntity',
            $jobs[0]
        );

        $i = 0;
        foreach ($jobs as $jobName => $jobEntity) {
            $this->assertEquals(
                $this->listingJobs[$i]['name'],
                $jobEntity->name
            );

            ++$i;
        }
    }

    public function testAddJobSuccess()
    {
        $this->jobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn([])
        ;

        $this->apiClient
            ->addingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertTrue($jobRepositoryChronos->addJob(new ChronosJobEntity()));
    }

    public function testAddJobFailed()
    {
        $this->jobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(['sProperty'])
        ;

        $this->apiClient
            ->addingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldNotBeCalled()
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertFalse($jobRepositoryChronos->addJob(new ChronosJobEntity()));
    }

    public function testUpdateJobSuccess()
    {
        $this->jobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn([])
        ;

        $this->apiClient
            ->updatingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertTrue($jobRepositoryChronos->updateJob(new ChronosJobEntity()));
    }

    public function testUpdateJobFailure()
    {
        $this->jobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(['sProperty'])
        ;

        $this->apiClient
            ->updatingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldNotBeCalled()
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertFalse($jobRepositoryChronos->updateJob(new ChronosJobEntity()));
    }

    public function testRemoveJobSuccess()
    {
        $this->apiClient
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->apiClient
            ->removeJob(Argument::exact(''))
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertTrue($jobRepositoryChronos->removeJob($this->getValidScheduledJobEntity('JobA')));
        $this->assertFalse($jobRepositoryChronos->removeJob(new ChronosJobEntity()));
    }

    public function testRemoveJobFailure()
    {
        $this->apiClient
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $this->cache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $jobRepositoryChronos = new BridgeChronos(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobEntityValidatorService->reveal(),
            $this->logger->reveal()
        );

        $this->assertFalse($jobRepositoryChronos->removeJob($this->getValidScheduledJobEntity('JobA')));
    }
}
