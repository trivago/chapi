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
    private $apiClient;
    /**
     * @var JobValidatorServiceInterface
     */
    private $jobEntityValidatorService;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $cacheHasToDelete = false;

    public function __construct(
        ApiClientInterface $apiClient,
        CacheInterface $cache,
        JobValidatorServiceInterface $jobEntityValidatorService,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->jobEntityValidatorService = $jobEntityValidatorService;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function __destruct()
    {
        if ($this->cacheHasToDelete) {
            $this->cache->delete(self::CACHE_KEY_APP_LIST);
        }
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        $apps = [];
        $jobsList = $this->getJobList();

        if (!empty($jobsList)) {
            foreach ($jobsList as $jobData) {
                $apps[] = new MarathonAppEntity($jobData);
            }
        }
        return $apps;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $jobEntity)
    {
        if ($this->apiClient->addingJob($jobEntity)) {
            $this->cacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity)
    {
        if ($this->apiClient->updatingJob($jobEntity)) {
            $this->cacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $jobEntity)
    {
        if ($this->apiClient->removeJob($jobEntity->getKey())) {
            $this->cacheHasToDelete = true;
            return true;
        }
        return false;
    }

    /**
     * @return array|mixed
     */
    private function getJobList()
    {
        $result = $this->cache->get(self::CACHE_KEY_APP_LIST);

        if (is_array($result)) {
            return $result;
        }

        $result = $this->apiClient->listingJobs();

        if (!empty($result['apps'])) {
            $this->cache->set(self::CACHE_KEY_APP_LIST, $result['apps'], self::CACHE_TIME_JOB_LIST);
        }

        return $result['apps'];
    }
}
