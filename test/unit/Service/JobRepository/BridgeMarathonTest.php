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

class BridgeMarathonTest extends \PHPUnit_Framework_TestCase
{
    use AppEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oApiClient;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oCache;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oJobValidatorService;

    /** @var  \Prophecy\Prophecy\ObjectProphecy */
    private $oLogger;

    private $sJsonApps = '{"apps":[{"id":"/bots/runner","cmd":"chmod +x run.sh && ./run.sh","args":null,"user":null,"env":{"APP_GROUP":"131441"},"instances":1,"cpus":0.5,"mem":128,"disk":30,"executor":"","constraints":[],"uris":["https://raw.githubusercontent.com/test/testrepo/master/run.sh"],"fetch":[{"uri":"https://raw.githubusercontent.com/test/testrepo/master/run.sh","extract":false,"executable":false,"cache":false}],"storeUrls":[],"ports":[10310],"portDefinitions":[{"port":10310,"protocol":"tcp","labels":{}}],"requirePorts":false,"backoffSeconds":1,"backoffFactor":1.15,"maxLaunchDelaySeconds":3600,"container":null,"healthChecks":[{"path":"/health","protocol":"HTTP","portIndex":0,"gracePeriodSeconds":60,"intervalSeconds":10,"timeoutSeconds":10,"maxConsecutiveFailures":3,"ignoreHttp1xx":false}],"readinessChecks":[],"dependencies":[],"upgradeStrategy":{"minimumHealthCapacity":1,"maximumOverCapacity":1},"labels":{"app_label":"operation"},"acceptedResourceRoles":null,"ipAddress":null,"version":"2016-08-02T08:15:37.666Z","residency":null,"versionInfo":{"lastScalingAt":"2016-08-02T08:15:37.666Z","lastConfigChangeAt":"2016-08-02T08:15:37.666Z"},"tasksStaged":0,"tasksRunning":1,"tasksHealthy":1,"tasksUnhealthy":0,"deployments":[]}]}';

    private $aListingJobs;
    public function setup()
    {
        $this->aListingJobs = json_decode($this->sJsonApps, true);
        $this->oApiClient = $this->prophesize('Chapi\Component\RemoteClients\ApiClientInterface');

        $this->oApiClient
            ->listingJobs()
            ->willReturn($this->aListingJobs);

        $this->oCache = $this->prophesize('Chapi\Component\Cache\CacheInterface');
        $this->oJobValidatorService = $this->prophesize('Chapi\Service\JobValidator\JobValidatorServiceInterface');
        $this->oLogger = $this->prophesize('Psr\Log\LoggerInterface');
    }

    public function testGetJobsSuccess()
    {
        $this->oCache
            ->get(Argument::exact(BridgeMarathon::CACHE_KEY_APP_LIST))
            ->willReturn([]);

        $this->oCache
            ->set(BridgeMarathon::CACHE_KEY_APP_LIST, Argument::exact($this->aListingJobs["apps"]), BridgeMarathon::CACHE_TIME_JOB_LIST);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_aApps = $_oMarathonBridge->getJobs();

        foreach($_aApps as $_gotApp)
        {
            $this->assertInstanceOf('Chapi\Entity\JobEntityInterface', 'Entity expected to be fullfill of JobEntityInterface interface');
            $this->assertInstanceOf('Chapi\Entity\Marathon\MarathonAppEntity', 'Entity expected to be instance of MarathonAppEntity');

            $_bFound = false;
            foreach($this->aListingJobs["apps"] as $_listedApp)
            {
                if ($_gotApp->getKey() == $_listedApp["id"])
                {
                    $_bFound = true;
                    break;
                }
            }
            $this->assertTrue($_bFound, 'Expected job not found');
        }
    }


    public function testAddJobSuccess()
    {
        $_oApp = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->oApiClient
            ->addingJob(Argument::exact($_oApp))
            ->willReturn(true);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->addJob($_oApp);

        $this->assertTrue($_bSuccess, "Expected addJob to return true, false returned");

    }

    public function testAddJobSuccessFailure()
    {
        $_oApp = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->oApiClient
            ->addingJob(Argument::exact($_oApp))
            ->willReturn(false);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->addJob($_oApp);

        $this->assertFalse($_bSuccess, "Expected addJob to return false, true returned");
    }

    public function updateJobSuccess()
    {
        $_oApp = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->oApiClient
            ->updatingJob(Argument::exact($_oApp))
            ->willReturn(true);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->updateJob($_oApp);

        $this->assertTrue($_bSuccess, "Expected addJob to return true, false returned");
    }

    public function updateJobFailure()
    {
        $_oApp = $this->getValidMarathonAppEntity("/mygroup/myapp");

        $this->oApiClient
            ->updatingJob(Argument::exact($_oApp))
            ->willReturn(false);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->updateJob($_oApp);

        $this->assertFalse($_bSuccess, "Expected updateJob to return false, true returned");
    }


    public function testRemoveJobSuccess()
    {
        $_oAppKey = "/mygroup/myapp";
        $_oApp = $this->getValidMarathonAppEntity($_oAppKey);

        $this->oApiClient
            ->removeJob(Argument::exact($_oAppKey))
            ->willReturn(true);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->removeJob($_oApp);
        $this->assertTrue($_bSuccess, "Expected true, false returned");
    }

    public function testRemoveJobFailure()
    {
        $_oAppKey = "/mygroup/myapp";
        $_oApp = $this->getValidMarathonAppEntity($_oAppKey);

        $this->oApiClient
            ->removeJob(Argument::exact($_oAppKey))
            ->willReturn(false);

        $_oMarathonBridge = new BridgeMarathon(
            $this->oApiClient->reveal(),
            $this->oCache->reveal(),
            $this->oJobValidatorService->reveal(),
            $this->oLogger->reveal()
        );

        $_bSuccess = $_oMarathonBridge->removeJob($_oApp);
        $this->assertFalse($_bSuccess, "Expected false, true returned");
    }

}
