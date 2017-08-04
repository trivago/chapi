<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-09
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Service\Chronos;

use Chapi\Entity\Chronos\JobStatsEntity;

interface JobStatsServiceInterface
{
    const DIC_NAME = 'JobStatsServiceInterface';

    /**
     * @param string $sJobName
     * @return JobStatsEntity
     */
    public function getJobStats($sJobName);
}
