<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Service\Chronos;

interface JobServiceInterface
{
    const DIC_NAME = 'JobServiceInterface';

    /**
     * @param string $sJobName
     * @return \Chapi\Entity\Chronos\JobEntity
     */
    public function getJob($sJobName);

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs();
}