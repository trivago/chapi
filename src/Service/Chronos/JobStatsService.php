<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-09
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Service\Chronos;

use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Entity\Chronos\JobStatsEntity;

class JobStatsService implements JobStatsServiceInterface
{
    const CACHE_TIME_JOB_STATS = 900;

    const CACHE_KEY_JOB_STATS = 'jobs.stats.%s';

    /**
     * @var \Chapi\Component\RemoteClients\ApiClientInterface
     */
    private $apiClient;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param \Chapi\Component\RemoteClients\ApiClientInterface $apiClient
     * @param CacheInterface $cache
     */
    public function __construct(
        \Chapi\Component\RemoteClients\ApiClientInterface $apiClient,
        CacheInterface $cache
    ) {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
    }

    /**
     * @param $jobName
     * @return JobStatsEntity
     */
    public function getJobStats($jobName)
    {
        $cacheKey = sprintf(self::CACHE_KEY_JOB_STATS, $jobName);
        $stats = $this->cache->get($cacheKey);

        if (empty($stats)) {
            $stats = $this->apiClient->getJobStats($jobName);

            // set result to cache
            if (!empty($stats)) {
                $this->cache->set($cacheKey, $stats, self::CACHE_TIME_JOB_STATS);
            }
        }

        return new JobStatsEntity($stats);
    }
}
