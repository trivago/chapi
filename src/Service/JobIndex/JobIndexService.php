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
    private $oCache;

    /**
     * @var array
     */
    private $aJobIndex = [];

    /**
     * @param CacheInterface $oCache
     */
    public function __construct(
        CacheInterface $oCache
    ) {
        $this->oCache = $oCache;
        $this->aJobIndex = $this->getJobIndexFromStorage();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->setJobIndexToStorage();
    }

    /**
     * @param string $sJobName
     * @return $this
     */
    public function addJob($sJobName)
    {
        $this->aJobIndex[$sJobName] = $sJobName;
        return $this;
    }

    /**
     * @param array $aJobNames
     * @return $this
     */
    public function addJobs(array $aJobNames)
    {
        foreach ($aJobNames as $_sJobName) {
            $this->addJob($_sJobName);
        }

        return $this;
    }

    /**
     * @param string $sJobName
     * @return $this
     */
    public function removeJob($sJobName)
    {
        if (isset($this->aJobIndex[$sJobName])) {
            unset($this->aJobIndex[$sJobName]);
        }

        return $this;
    }

    /**
     * @param array $aJobNames
     * @return $this
     */
    public function removeJobs(array $aJobNames)
    {
        foreach ($aJobNames as $_sJobName) {
            $this->removeJob($_sJobName);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetJobIndex()
    {
        $this->aJobIndex = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getJobIndex()
    {
        return $this->aJobIndex;
    }

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobInIndex($sJobName)
    {
        return (isset($this->aJobIndex[$sJobName]));
    }

    /**
     * @return array
     */
    private function getJobIndexFromStorage()
    {
        $_aJobIndex = $this->oCache->get(self::JOB_INDEX_CACHE_KEY);
        return (is_array($_aJobIndex)) ? $_aJobIndex : [];
    }

    /**
     *
     */
    private function setJobIndexToStorage()
    {
        $this->oCache->set(self::JOB_INDEX_CACHE_KEY, $this->aJobIndex);
    }
}
