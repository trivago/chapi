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
    private $oApiClient;

    /**
     * @var CacheInterface
     */
    private $oCache;

    /**
     * @param \Chapi\Component\RemoteClients\ApiClientInterface $oApiClient
     * @param CacheInterface $oCache
     */
    public function __construct(
        \Chapi\Component\RemoteClients\ApiClientInterface $oApiClient,
        CacheInterface $oCache
    )
    {
        $this->oApiClient = $oApiClient;
        $this->oCache = $oCache;
    }

    /**
     * @param $sJobName
     * @return JobStatsEntity
     */
    public function getJobStats($sJobName)
    {
        $_sCacheKey = sprintf(self::CACHE_KEY_JOB_STATS, $sJobName);
        $_aStats = $this->oCache->get($_sCacheKey);

        if (empty($_aStats))
        {
            $_aStats = $this->oApiClient->getJobStats($sJobName);

            // set result to cache
            if (!empty($_aStats))
            {
                $this->oCache->set($_sCacheKey, $_aStats, self::CACHE_TIME_JOB_STATS);
            }
        }

        return new JobStatsEntity($_aStats);
    }
}