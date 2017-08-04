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
     * @param string $jobName
     * @return JobEntityInterface
     */
    public function getJob($jobName);

    /**
     * @return JobCollection
     */
    public function getJobs();

    /**
     * @param string $jobName
     * @return bool
     */
    public function hasJob($jobName);

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $jobEntity);

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity);

    /**
     * @param string $jobName
     * @return bool
     */
    public function removeJob($jobName);
}
