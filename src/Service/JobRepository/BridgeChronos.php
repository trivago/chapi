<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Service\JobRepository;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\Chronos\ApiClientInterface;
use Chapi\Entity\Chronos\JobEntity;
use Psr\Log\LoggerInterface;

class BridgeChronos implements BridgeInterface
{
    const CACHE_TIME_JOB_LIST = 60;

    const CACHE_KEY_JOB_LIST = 'jobs.list';

    const API_CALL_ADD = 'addingJob';

    const API_CALL_UPDATE = 'updatingJob';

    /**
     * @var ApiClientInterface
     */
    private $oApiClient;

    /**
     * @var CacheInterface
     */
    private $oCache;

    /**
     * @var JobEntityValidatorServiceInterface
     */
    private $oJobEntityValidatorService;

    /**
     * @var bool
     */
    private $bCacheHasToDelete = false;

    /**
     * @var LoggerInterface
     */
    private $oLogger;

    /**
     * @param ApiClientInterface $oApiClient
     * @param CacheInterface $oCache
     * @param JobEntityValidatorServiceInterface $oJobEntityValidatorService
     * @param LoggerInterface $oLogger
     */
    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobEntityValidatorServiceInterface $oJobEntityValidatorService,
        LoggerInterface $oLogger
    )
    {
        $this->oApiClient = $oApiClient;
        $this->oCache = $oCache;
        $this->oJobEntityValidatorService = $oJobEntityValidatorService;
        $this->oLogger = $oLogger;
    }

    /**
     * delete cache job.list if a job was removed
     */
    public function __destruct()
    {
        if ($this->bCacheHasToDelete)
        {
            $this->oCache->delete(self::CACHE_KEY_JOB_LIST);
        }
    }


    /**
     * @return JobEntity[]
     */
    public function getJobs()
    {
        $_aReturn = [];
        $_aJobList = $this->getJobList();

        if (!empty($_aJobList))
        {
            // prepare return value
            foreach ($_aJobList as $_aJobData)
            {
                $_aReturn[] = new JobEntity($_aJobData);
            }
        }

        return $_aReturn;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addJob(JobEntity $oJobEntity)
    {
        return $this->addUpdateJob(self::API_CALL_ADD, $oJobEntity);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntity $oJobEntity)
    {
        return $this->addUpdateJob(self::API_CALL_UPDATE, $oJobEntity);
    }


    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntity $oJobEntity)
    {
        if ($this->oApiClient->removeJob($oJobEntity->name))
        {
            $this->bCacheHasToDelete = true;
            return true;
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    private function validate(JobEntity $oJobEntity)
    {
        $_aInvalidProperties = $this->oJobEntityValidatorService->getInvalidProperties($oJobEntity);
        if (empty($_aInvalidProperties))
        {
            return true;
        }

        $this->oLogger->warning(
            sprintf(
                "Can't update job '%s'",
                $oJobEntity->name
            )
        );
        $this->oLogger->warning(
            sprintf(
                "The following job entity properties are not valid:\n%s",
                implode(', ', $_aInvalidProperties)
            )
        );

        return false;
    }

    /**
     * @return array
     */
    private function getJobList()
    {
        $_aResult = $this->oCache->get(self::CACHE_KEY_JOB_LIST);

        if (is_array($_aResult))
        {
            // return list from cache
            return $_aResult;
        }

        $_aResult = $this->oApiClient->listingJobs();
        if (!empty($_aResult))
        {
            // set result to cache
            $this->oCache->set(self::CACHE_KEY_JOB_LIST, $_aResult, self::CACHE_TIME_JOB_LIST);
        }

        return $_aResult;
    }

    /**
     * @param string $sApiMethod
     * @param JobEntity $oJobEntity
     * @return bool
     */
    private function addUpdateJob($sApiMethod, JobEntity $oJobEntity)
    {
        if ($this->validate($oJobEntity))
        {
            if ($this->oApiClient->{$sApiMethod}($oJobEntity))
            {
                $this->bCacheHasToDelete = true;
                return true;
            }
        }

        return false;
    }
}