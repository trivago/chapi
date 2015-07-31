<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */


namespace Chapi\Component\Chronos;


use Chapi\Entity\Chronos\JobEntity;

interface ApiClientInterface
{
    const DIC_NAME = 'ApiClientInterface';

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return []
     */
    public function listingJobs();

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addingJob(JobEntity $oJobEntity);

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updatingJob(JobEntity $oJobEntity);

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName);
}