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

class BridgeChronosTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oApiClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oJobEntityValidatorService;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    private $sJsonListingJobs = '[{"name":"job-a","command":"echo job","shell":true,"epsilon":"PT1M","executor":"","executorFlags":"","retries":0,"owner":"mail@address.com","ownerName":"Foo","description":"Generates something","async":false,"successCount":10918,"errorCount":16,"lastSuccess":"2015-08-10T07:35:04.529Z","lastError":"2015-08-03T12:15:06.183Z","cpus":0.1,"disk":24.0,"mem":128.0,"disabled":false,"softError":false,"dataProcessingJobType":false,"errorsSinceLastSuccess":0,"uris":[],"environmentVariables":[],"arguments":[],"highPriority":false,"runAsUser":"root","schedule":"R/2015-08-10T09:40:00.000+02:00/PT5M","scheduleTimeZone":"Europe/Berlin"},{"name":"job-b","command":"echo jobb","shell":true,"epsilon":"PT60S","executor":"","executorFlags":"","retries":0,"owner":"mail@address.com","ownerName":"Bar","description":"Do another thing","async":false,"successCount":1145,"errorCount":3,"lastSuccess":"2015-08-10T07:15:11.275Z","lastError":"2015-08-03T12:15:06.146Z","cpus":0.1,"disk":24.0,"mem":64.0,"disabled":false,"softError":false,"dataProcessingJobType":false,"errorsSinceLastSuccess":0,"uris":[],"environmentVariables":[],"arguments":[],"highPriority":false,"runAsUser":"root","schedule":"R/2015-08-10T09:45:00.000+02:00/PT30M","scheduleTimeZone":"Europe/Berlin"}]';

    private $aListingJobs = [];

    public function setUp()
    {
        $this->aListingJobs = json_decode($this->sJsonListingJobs, true);

        $this->oApiClient = $this->prophesize('Chapi\Component\RemoteClients\ApiClientInterface');
        $this->oApiClient
            ->listingJobs()
            ->willReturn($this->aListingJobs)
        ;

        $this->oCache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
        $this->oCache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->willReturn(null)
        ;

        $this->oCache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->aListingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->willReturn(true)
        ;

        $this->oJobEntityValidatorService = $this->prophesize('Chapi\Service\JobValidator\ChronosJobValidatorService');

        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testGetJobsSuccess()
    {
        $this->oApiClient
            ->listingJobs()
            ->shouldBeCalledTimes(1)
        ;

        $this->oCache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
        ;

        $this->oCache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->aListingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->shouldBeCalledTimes(1)
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_aJobs = $_oJobRepositoryChronos->getJobs();

        $this->assertInternalType(
            'array',
            $_aJobs
        );

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\ChronosJobEntity',
            $_aJobs[0]
        );

        $_i = 0;
        foreach ($_aJobs as $_sJobName => $_oJobEntity)
        {

            $this->assertEquals(
                $this->aListingJobs[$_i]['name'],
                $_oJobEntity->name
            );

            ++$_i;
        }
    }

    public function testGetJobsWithCacheSuccess()
    {
        $this->oApiClient
            ->listingJobs()
            ->shouldNotBeCalled()
        ;

        $this->oCache
            ->get(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->aListingJobs)
        ;

        $this->oCache
            ->set(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST), Argument::exact($this->aListingJobs), BridgeChronos::CACHE_TIME_JOB_LIST)
            ->shouldNotBeCalled()
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_aJobs = $_oJobRepositoryChronos->getJobs();

        $this->assertInternalType(
            'array',
            $_aJobs
        );

        $this->assertInstanceOf(
            'Chapi\Entity\Chronos\ChronosJobEntity',
            $_aJobs[0]
        );

        $_i = 0;
        foreach ($_aJobs as $_sJobName => $_oJobEntity)
        {

            $this->assertEquals(
                $this->aListingJobs[$_i]['name'],
                $_oJobEntity->name
            );

            ++$_i;
        }
    }

    public function testAddJobSuccess()
    {
        $this->oJobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn([])
        ;

        $this->oApiClient
            ->addingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertTrue($_oJobRepositoryChronos->addJob(new ChronosJobEntity()));
    }

    public function testAddJobFailed()
    {
        $this->oJobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(['sProperty'])
        ;

        $this->oApiClient
            ->addingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldNotBeCalled()
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertFalse($_oJobRepositoryChronos->addJob(new ChronosJobEntity()));
    }

    public function testUpdateJobSuccess()
    {
        $this->oJobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn([])
        ;

        $this->oApiClient
            ->updatingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertTrue($_oJobRepositoryChronos->updateJob(new ChronosJobEntity()));
    }

    public function testUpdateJobFailure()
    {
        $this->oJobEntityValidatorService
            ->getInvalidProperties(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldBeCalledTimes(1)
            ->willReturn(['sProperty'])
        ;

        $this->oApiClient
            ->updatingJob(Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))
            ->shouldNotBeCalled()
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertFalse($_oJobRepositoryChronos->updateJob(new ChronosJobEntity()));
    }

    public function testRemoveJobSuccess()
    {
        $this->oApiClient
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn(true)
        ;

        $this->oApiClient
            ->removeJob(Argument::exact(''))
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldBeCalledTimes(1)
            ->willReturn(null)
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertTrue($_oJobRepositoryChronos->removeJob($this->getValidScheduledJobEntity('JobA')));
        $this->assertFalse($_oJobRepositoryChronos->removeJob(new ChronosJobEntity()));
    }

    public function testRemoveJobFailure()
    {
        $this->oApiClient
            ->removeJob(Argument::exact('JobA'))
            ->shouldBeCalledTimes(1)
            ->willReturn(false)
        ;

        $this->oCache
            ->delete(Argument::exact(BridgeChronos::CACHE_KEY_JOB_LIST))
            ->shouldNotBeCalled()
        ;

        $_oJobRepositoryChronos = new BridgeChronos(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobEntityValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $this->assertFalse($_oJobRepositoryChronos->removeJob($this->getValidScheduledJobEntity('JobA')));
    }
}