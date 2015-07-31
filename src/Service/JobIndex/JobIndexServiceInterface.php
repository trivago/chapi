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
     * @param string $sJobName
     * @return mixed
     */
    public function addJob($sJobName);

    /**
     * @param array $aJobNames
     * @return mixed
     */
    public function addJobs(array $aJobNames);

    /**
     * @param string $sJobName
     * @return mixed
     */
    public function removeJob($sJobName);

    /**
     * @param array $aJobNames
     * @return mixed
     */
    public function removeJobs(array $aJobNames);

    /**
     * @return mixed
     */
    public function resetJobIndex();

    /**
     * @return array
     */
    public function getJobIndex();

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobInIndex($sJobName);
}