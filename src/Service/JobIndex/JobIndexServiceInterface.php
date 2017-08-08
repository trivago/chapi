<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobIndex;

interface JobIndexServiceInterface
{
    const DIC_NAME = 'JobIndexServiceInterface';

    /**
     * @param string $jobName
     * @return JobIndexServiceInterface
     */
    public function addJob($jobName);

    /**
     * @param array $jobNames
     * @return JobIndexServiceInterface
     */
    public function addJobs(array $jobNames);

    /**
     * @param string $jobName
     * @return JobIndexServiceInterface
     */
    public function removeJob($jobName);

    /**
     * @param array $jobNames
     * @return JobIndexServiceInterface
     */
    public function removeJobs(array $jobNames);

    /**
     * @return JobIndexServiceInterface
     */
    public function resetJobIndex();

    /**
     * @return array
     */
    public function getJobIndex();

    /**
     * @param $jobName
     * @return bool
     */
    public function isJobInIndex($jobName);
}
