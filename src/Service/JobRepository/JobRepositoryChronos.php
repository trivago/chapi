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
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\JobEntity;

class JobRepositoryChronos implements JobRepositoryServiceInterface
{
    const CACHE_TIME_JOB_LIST = 60;

    const CACHE_KEY_JOB_LIST = 'jobs.list';

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
     * @var JobCollection
     */
    private $oJobCollection;

    /**
     * @var bool
     */
    private $bJobsRemoved = false;

    /**
     * @param ApiClientInterface $oApiClient
     * @param CacheInterface $oCache
     * @param JobEntityValidatorServiceInterface $oJobEntityValidatorService
     */
    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobEntityValidatorServiceInterface $oJobEntityValidatorService
    )
    {
        $this->oApiClient = $oApiClient;
        $this->oCache = $oCache;
        $this->oJobEntityValidatorService = $oJobEntityValidatorService;
    }

    /**
     * delete cache job.list if a job was removed
     */
    public function __destruct()
    {
        if ($this->bJobsRemoved)
        {
            $this->oCache->delete(self::CACHE_KEY_JOB_LIST);
        }
    }


    /**
     * @param string $sJobName
     * @return JobEntity
     */
    public function getJob($sJobName)
    {
        $_aJobs = $this->getJobs();
        if (isset($_aJobs[$sJobName]))
        {
            return $_aJobs[$sJobName];
        }

        return new JobEntity();
    }

    /**
     * @return JobCollection
     */
    public function getJobs()
    {
        if (!is_null($this->oJobCollection))
        {
            return $this->oJobCollection;
        }

        $_aReturn = [];
        $_aJobList = $this->getJobList();

        if (!empty($_aJobList))
        {
            // prepare return value
            foreach ($_aJobList as $_aJobData)
            {
                $_sJobName = $_aJobData['name'];
                $_aReturn[$_sJobName] = new JobEntity($_aJobData);
            }
        }

        return $this->oJobCollection = new JobCollection($_aReturn);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return mixed
     */
    public function addJob(JobEntity $oJobEntity)
    {
        if ($this->oJobEntityValidatorService->isEntityValid($oJobEntity))
        {
            $_bAddingJob = $this->oApiClient->addingJob($oJobEntity);
            return $_bAddingJob;
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return mixed
     */
    public function updateJob(JobEntity $oJobEntity)
    {
        if ($this->oJobEntityValidatorService->isEntityValid($oJobEntity))
        {
            $_bUpdatingJob = $this->oApiClient->updatingJob($oJobEntity);
            return $_bUpdatingJob;
        }

        return false;
    }

    /**
     * @param string $sJobName
     * @return mixed
     */
    public function removeJob($sJobName)
    {
        if (!empty($sJobName))
        {
            if ($this->oApiClient->removeJob($sJobName))
            {
                $this->bJobsRemoved = true;
                return true;
            }
        }

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
}