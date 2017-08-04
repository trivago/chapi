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
    private $apiClient;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var JobValidatorServiceInterface
     */
    private $jobEntityValidatorService;

    /**
     * @var bool
     */
    private $cacheHasToDelete = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Chapi\Component\RemoteClients\ApiClientInterface $apiClient
     * @param CacheInterface $cache
     * @param JobValidatorServiceInterface $jobEntityValidatorService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ApiClientInterface $apiClient,
        CacheInterface $cache,
        JobValidatorServiceInterface $jobEntityValidatorService,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->cache = $cache;
        $this->jobEntityValidatorService = $jobEntityValidatorService;
        $this->logger = $logger;
    }

    /**
     * delete cache job.list if a job was removed
     */
    public function __destruct()
    {
        if ($this->cacheHasToDelete) {
            $this->cache->delete(self::CACHE_KEY_JOB_LIST);
        }
    }


    /**
     * @return ChronosJobEntity[]
     */
    public function getJobs()
    {
        $return = [];
        $jobList = $this->getJobList();

        if (!empty($jobList)) {
            // prepare return value
            foreach ($jobList as $jobData) {
                $return[] = new ChronosJobEntity($jobData);
            }
        }

        return $return;
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $jobEntity)
    {
        return $this->hasAddOrUpdateJob(self::API_CALL_ADD, $jobEntity);
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity)
    {
        return $this->hasAddOrUpdateJob(self::API_CALL_UPDATE, $jobEntity);
    }


    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
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
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function hasValidate(JobEntityInterface $jobEntity)
    {
        $invalidProperties = $this->jobEntityValidatorService->getInvalidProperties($jobEntity);
        if (empty($invalidProperties)) {
            return true;
        }

        $this->logger->warning(
            sprintf(
                "Can't update job '%s'",
                $jobEntity->getKey()
            )
        );
        $this->logger->warning(
            sprintf(
                "The following job entity properties are not valid:\n%s",
                implode(', ', $invalidProperties)
            )
        );

        return false;
    }

    /**
     * @return array
     */
    private function getJobList()
    {
        $result = $this->cache->get(self::CACHE_KEY_JOB_LIST);

        if (is_array($result)) {
            // return list from cache
            return $result;
        }

        $result = $this->apiClient->listingJobs();
        if (!empty($result)) {
            // set result to cache
            $this->cache->set(self::CACHE_KEY_JOB_LIST, $result, self::CACHE_TIME_JOB_LIST);
        }

        return $result;
    }

    /**
     * @param string $apiMethod
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function hasAddOrUpdateJob($apiMethod, JobEntityInterface $jobEntity)
    {
        if ($this->hasValidate($jobEntity)) {
            if ($this->apiClient->{$apiMethod}($jobEntity)) {
                $this->cacheHasToDelete = true;
                return true;
            }
        }

        return false;
    }
}
