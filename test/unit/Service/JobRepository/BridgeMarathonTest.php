<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-11
 *
 */

namespace unit\Service\JobRepository;

use Chapi\Service\JobRepository\BridgeMarathon;
use ChapiTest\src\TestTraits\AppEntityTrait;
use Prophecy\Argument;

class BridgeMarathonTest extends \PHPUnit\Framework\TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $apiClient;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $cache;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $jobValidatorService;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $logger;

    private $jsonApps = '{"apps":[{"id":"/bots/runner","cmd":"chmod +x run.sh && ./run.sh","args":null,"user":null,"env":{"APP_GROUP":"131441"},"instances":1,"cpus":0.5,"mem":128,"disk":30,"executor":"","constraints":[],"uris":["https://raw.githubusercontent.com/test/testrepo/master/run.sh"],"fetch":[{"uri":"https://raw.githubusercontent.com/test/testrepo/master/run.sh","extract":false,"executable":false,"cache":false}],"storeUrls":[],"ports":[10310],"portDefinitions":[{"port":10310,"protocol":"tcp","labels":{}}],"requirePorts":false,"backoffSeconds":1,"backoffFactor":1.15,"maxLaunchDelaySeconds":3600,"container":null,"healthChecks":[{"path":"/health","protocol":"HTTP","portIndex":0,"gracePeriodSeconds":60,"intervalSeconds":10,"timeoutSeconds":10,"maxConsecutiveFailures":3,"ignoreHttp1xx":false}],"readinessChecks":[],"dependencies":[],"upgradeStrategy":{"minimumHealthCapacity":1,"maximumOverCapacity":1},"labels":{"app_label":"operation"},"acceptedResourceRoles":null,"ipAddress":null,"version":"2016-08-02T08:15:37.666Z","residency":null,"versionInfo":{"lastScalingAt":"2016-08-02T08:15:37.666Z","lastConfigChangeAt":"2016-08-02T08:15:37.666Z"},"tasksStaged":0,"tasksRunning":1,"tasksHealthy":1,"tasksUnhealthy":0,"deployments":[]}]}';

    private $listingJobs;

    protected function setUp(): void
    {
        $this->listingJobs = json_decode($this->jsonApps, true);
        $this->apiClient = $this->prophesize('Chapi\Component\RemoteClients\ApiClientInterface');

        $this->apiClient
            ->listingJobs()
            ->willReturn($this->listingJobs);


        $this->cache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
        $this->jobValidatorService = $this->prophesize('Chapi\Service\JobValidator\JobValidatorServiceInterface');
        $this->logger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testGetJobsSuccess()
    {
        $this->cache
            ->get(Argument::exact(BridgeMarathon::CACHE_KEY_APP_LIST))
            ->willReturn([]);

        $this->cache
            ->set(BridgeMarathon::CACHE_KEY_APP_LIST, Argument::exact($this->listingJobs["apps"]), BridgeMarathon::CACHE_TIME_JOB_LIST);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $apps = $marathonBridge->getJobs();

        foreach ($apps as $gotApp) {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', 'Entity expected to be fullfill of JobEntityInterface interface');
            $this->assertInstanceOf('Chapi\Entity\Marathon\MarathonAppEntity', 'Entity expected to be instance of MarathonAppEntity');

            $found = false;
            foreach ($this->listingJobs["apps"] as $listedApp) {
                if ($gotApp->getKey() == $listedApp["id"]) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Expected job not found');
        }
    }


    public function testAddJobSuccess()
    {
        $app = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->apiClient
            ->addingJob(Argument::exact($app))
            ->willReturn(true);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->addJob($app);

        $this->assertTrue($success, "Expected addJob to return true, false returned");
    }

    public function testAddJobSuccessFailure()
    {
        $app = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->apiClient
            ->addingJob(Argument::exact($app))
            ->willReturn(false);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->addJob($app);

        $this->assertFalse($success, "Expected addJob to return false, true returned");
    }

    public function updateJobSuccess()
    {
        $app = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->apiClient
            ->updatingJob(Argument::exact($app))
            ->willReturn(true);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->updateJob($app);

        $this->assertTrue($success, "Expected addJob to return true, false returned");
    }

    public function updateJobFailure()
    {
        $app = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->apiClient
            ->updatingJob(Argument::exact($app))
            ->willReturn(false);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->updateJob($app);

        $this->assertFalse($success, "Expected updateJob to return false, true returned");
    }


    public function testRemoveJobSuccess()
    {
        $appKey = "/mygroup/myapp";
        $app = $this->getValidMarathonAppEntity($appKey);

        $this->apiClient
            ->removeJob(Argument::exact($appKey))
            ->willReturn(true);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->removeJob($app);
        $this->assertTrue($success, "Expected true, false returned");
    }

    public function testRemoveJobFailure()
    {
        $appKey = "/mygroup/myapp";
        $app = $this->getValidMarathonAppEntity($appKey);

        $this->apiClient
            ->removeJob(Argument::exact($appKey))
            ->willReturn(false);

        $marathonBridge = new BridgeMarathon(
            $this->apiClient->reveal(),
            $this->cache->reveal(),
            $this->jobValidatorService->reveal(),
            $this->logger->reveal()
        );

        $success = $marathonBridge->removeJob($app);
        $this->assertFalse($success, "Expected false, true returned");
    }
}
