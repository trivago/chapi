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
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;
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
     * @var JobValidatorServiceInterface
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
     * @param \Chapi\Component\RemoteClients\ApiClientInterface $oApiClient
     * @param CacheInterface $oCache
     * @param JobValidatorServiceInterface $oJobEntityValidatorService
     * @param LoggerInterface $oLogger
     */
    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobValidatorServiceInterface $oJobEntityValidatorService,
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
     * @return ChronosJobEntity[]
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
                $_aReturn[] = new ChronosJobEntity($_aJobData);
            }
        }

        return $_aReturn;
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        return $this->hasAddOrUpdateJob(self::API_CALL_ADD, $oJobEntity);
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        return $this->hasAddOrUpdateJob(self::API_CALL_UPDATE, $oJobEntity);
    }


    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
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
     * @param ChronosJobEntity $oJobEntity
     * @return bool
     */
    private function hasValidate(ChronosJobEntity $oJobEntity)
    {
        $_aInvalidProperties = $this->oJobEntityValidatorService->getInvalidProperties($oJobEntity);
        if (empty($_aInvalidProperties))
        {
            return true;
        }

        $this->oLogger->warning(
            sprintf(
                "Can't update job '%s'",
                $oJobEntity->getKey()
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
     * @param ChronosJobEntity $oJobEntity
     * @return bool
     */
    private function hasAddOrUpdateJob($sApiMethod, ChronosJobEntity $oJobEntity)
    {
        if ($this->hasValidate($oJobEntity))
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