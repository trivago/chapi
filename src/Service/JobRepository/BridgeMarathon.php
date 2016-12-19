<?php
/**
 * @package: chapi
 *
 * @author:  bthapaliya
 * @since:   2016-12-02
 */

namespace Chapi\Service\JobRepository;


use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;
use Psr\Log\LoggerInterface;

class BridgeMarathon implements BridgeInterface
{
    const CACHE_TIME_JOB_LIST = 60;

    const CACHE_KEY_APP_LIST = "marathon.app.list";

    /**
     * @var \Chapi\Component\RemoteClients\ApiClientInterface
     */
    private $oApiClient;
    /**
     * @var JobEntityValidatorServiceInterface
     */
    private $oJobEntityValidatorService;
    /**
     * @var CacheInterface
     */
    private $oCache;
    /**
     * @var LoggerInterface
     */
    private $oLogger;

    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobValidatorServiceInterface $oJobEntityValidatorService,
        LoggerInterface $oLogger
    )
    {

        $this->oApiClient = $oApiClient;
        $this->oJobEntityValidatorService = $oJobEntityValidatorService;
        $this->oCache = $oCache;
        $this->oLogger = $oLogger;
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        $_aApps = [];
        $_aJobsList = $this->getJobList();

        if (!empty($_aJobsList))
        {
            foreach ($_aJobsList as $_aJobData)
            {
                $_aApps[] = new MarathonAppEntity($_aJobData);
            }
        }
        return $_aApps;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement addJob() method.
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement updateJob() method.
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement removeJob() method.
    }

    private function getJobList()
    {
        $_aResult = $this->oCache->get(self::CACHE_KEY_APP_LIST);

        if (is_array($_aResult))
        {
            return $_aResult;
        }

        $_aResult = $this->oApiClient->listingJobs();

        if (!empty($_aResult->apps))
        {
            $this->oCache->set(self::CACHE_KEY_APP_LIST, $_aResult->apps, self::CACHE_TIME_JOB_LIST);
        }

        return $_aResult->apps;

    }

}