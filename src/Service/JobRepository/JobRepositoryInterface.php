<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */


namespace Chapi\Service\JobRepository;

use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\JobEntityInterface;

interface JobRepositoryInterface
{
    const DIC_NAME_CHRONOS = 'JobRepositoryChronos';
    const DIC_NAME_FILESYSTEM_CHRONOS = 'JobRepositoryFileSystemChronos';
    const DIC_NAME_FILESYSTEM_MARATHON = "JobRepositoryFileSystemMarathon";
    const DIC_NAME_MARATHON = 'JobRepositoryMarathon';

    /**
     * @param string $sJobName
     * @return JobEntityInterface
     */
    public function getJob($sJobName);

    /**
     * @return JobCollection
     */
    public function getJobs();

    /**
     * @param string $sJobName
     * @return bool
     */
    public function hasJob($sJobName);

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
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName);
}