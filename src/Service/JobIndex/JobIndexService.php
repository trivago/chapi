<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobIndex;

use Chapi\Component\Cache\CacheInterface;

class JobIndexService implements JobIndexServiceInterface
{
    const JOB_INDEX_CACHE_KEY = 'job.index';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $jobIndex = [];

    /**
     * @param CacheInterface $cache
     */
    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
        $this->jobIndex = $this->getJobIndexFromStorage();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->setJobIndexToStorage();
    }

    /**
     * @param string $jobName
     * @return $this
     */
    public function addJob($jobName)
    {
        $this->jobIndex[$jobName] = $jobName;
        return $this;
    }

    /**
     * @param array $jobNames
     * @return $this
     */
    public function addJobs(array $jobNames)
    {
        foreach ($jobNames as $jobName) {
            $this->addJob($jobName);
        }

        return $this;
    }

    /**
     * @param string $jobName
     * @return $this
     */
    public function removeJob($jobName)
    {
        if (isset($this->jobIndex[$jobName])) {
            unset($this->jobIndex[$jobName]);
        }

        return $this;
    }

    /**
     * @param array $jobNames
     * @return $this
     */
    public function removeJobs(array $jobNames)
    {
        foreach ($jobNames as $jobName) {
            $this->removeJob($jobName);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetJobIndex()
    {
        $this->jobIndex = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getJobIndex()
    {
        return $this->jobIndex;
    }

    /**
     * @param $jobName
     * @return bool
     */
    public function isJobInIndex($jobName)
    {
        return (isset($this->jobIndex[$jobName]));
    }

    /**
     * @return array
     */
    private function getJobIndexFromStorage()
    {
        $jobIndex = $this->cache->get(self::JOB_INDEX_CACHE_KEY);
        return (is_array($jobIndex)) ? $jobIndex : [];
    }

    /**
     *
     */
    private function setJobIndexToStorage()
    {
        $this->cache->set(self::JOB_INDEX_CACHE_KEY, $this->jobIndex);
    }
}
