<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */


namespace Chapi\Component\RemoteClients;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

interface ApiClientInterface
{
    const DIC_NAME = 'ApiClientInterface';

    const DIC_NAME_MARATHON = 'MarathonApiClientInterface';

    const DIC_NAME_CHRONOS = 'ChronosApiClientInterface';

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return array
     */
    public function listingJobs();

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function addingJob(JobEntityInterface $oJobEntity);

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function updatingJob(JobEntityInterface $oJobEntity);

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

    /**
     * Returns true if the client can be connected to.
     * @return bool
     */
    public function ping();
}
