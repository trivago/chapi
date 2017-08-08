<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\BusinessCase\JobManagement;

interface StoreJobBusinessCaseInterface
{
    const DIC_NAME = 'StoreJobBusinessCaseInterface';

    /**
     * @return void
     */
    public function storeIndexedJobs();

    /**
     * @param array $jobNames
     * @param bool|false $forceOverwrite
     * @return void
     */
    public function storeJobsToLocalRepository(array $jobNames = [], $forceOverwrite = false);

    /**
     * @param $jobName
     * @return bool
     */
    public function isJobAvailable($jobName);
}
