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

    /**
     * @var ApiClientInterface
     */
    private $oApiClient;

    /**
     * @var CacheInterface
     */
    private $oCache;

    /**
     * @param ApiClientInterface $oApiClient
     * @param CacheInterface $oCache
     */
    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache
    )
    {
        $this->oApiClient = $oApiClient;
        $this->oCache = $oCache;
    }


    public function addJob()
    {
        //TODO: implement method
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

        return [];
    }

    /**
     * @return JobCollection
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
                $_sJobName = $_aJobData['name'];
                $_aReturn[$_sJobName] = new JobEntity($_aJobData);
            }
        }

        return new JobCollection($_aReturn);
    }

    /**
     * @return array
     */
    private function getJobList()
    {
        $_sCacheKey = 'jobs.list';
        $_aResult = $this->oCache->get($_sCacheKey);

        if (is_array($_aResult))
        {
            // return list from cache
            return $_aResult;
        }

        $_aResult = $this->oApiClient->listingJobs();
        if (!empty($_aResult))
        {
            // set result to cache
            $this->oCache->set($_sCacheKey, $_aResult, self::CACHE_TIME_JOB_LIST);
        }

        return $_aResult;
    }
}