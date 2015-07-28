<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Service\Chronos;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\Chronos\ApiClientInterface;

class JobService implements JobServiceInterface
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

    public function getJob($sJobName)
    {
        //TODO: implement method
    }

    /**
     * @return array
     */
    public function getJobs()
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