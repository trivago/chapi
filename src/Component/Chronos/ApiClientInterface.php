<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */


namespace Chapi\Component\Chronos;


use Chapi\Entity\Chronos\ChronosJobEntity;

interface ApiClientInterface
{
    const DIC_NAME = 'ApiClientInterface';

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return array
     */
    public function listingJobs();

    /**
     * @param ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function addingJob(ChronosJobEntity $oJobEntity);

    /**
     * @param ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function updatingJob(ChronosJobEntity $oJobEntity);

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName);

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobStats($sJobName);
}