<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 */


namespace Chapi\Service\JobRepository;

use Chapi\Entity\Chronos\JobEntity;

interface BridgeInterface
{
    /**
     * @return JobEntity[]
     */
    public function getJobs();

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addJob(JobEntity $oJobEntity);

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntity $oJobEntity);

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntity $oJobEntity);
}