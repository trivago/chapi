<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 */


namespace Chapi\Service\JobRepository;

use Chapi\Entity\JobEntityInterface;

interface BridgeInterface
{
    /**
     * @return JobEntityInterface[]
     */
    public function getJobs();

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity);

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity);

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity);
}
