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

    const CACHE_KEY_APP_LIST = 'marathon.app.list';

    /**
     * @var \Chapi\Component\RemoteClients\ApiClientInterface
     */
    private $oApiClient;
    /**
     * @var JobValidatorServiceInterface
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

    private $bCacheHasToDelete = false;

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

    public function __destruct()
    {
        if ($this->bCacheHasToDelete)
        {
            $this->oCache->delete(self::CACHE_KEY_APP_LIST);
        }
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
        if ($this->oApiClient->addingJob($oJobEntity))
        {
            $this->bCacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        if ($this->oApiClient->updatingJob($oJobEntity))
        {
            $this->bCacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        if ($this->oApiClient->removeJob($oJobEntity->getKey()))
        {
            $this->bCacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @return array|mixed
     */
    private function getJobList()
    {
        $_aResult = $this->oCache->get(self::CACHE_KEY_APP_LIST);

        if (is_array($_aResult))
        {
            return $_aResult;
        }

        $_aResult = $this->oApiClient->listingJobs();

        if (!empty($_aResult['apps']))
        {
            $this->oCache->set(self::CACHE_KEY_APP_LIST, $_aResult['apps'], self::CACHE_TIME_JOB_LIST);
        }

        return $_aResult['apps'];

    }

}