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
     * @return mixed
     */
    public function getJob($sJobName);

    /**
     * @return array
     */
    public function getJobs();
}