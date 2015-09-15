<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */


namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\JobEntity;

interface JobRepositoryInterface
{
    const DIC_NAME_CHRONOS = 'JobRepositoryChronos';
    const DIC_NAME_FILESYSTEM = 'JobRepositoryFileSystem';

    /**
     * @param string $sJobName
     * @return JobEntity
     */
    public function getJob($sJobName);

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs();

    /**
     * @param string $sJobName
     * @return bool
     */
    public function hasJob($sJobName);

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
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName);
}