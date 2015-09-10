<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-10
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */

namespace Chapi\Service\JobDependencies;

interface JobDependencyServiceInterface
{
    const DIC_NAME = 'JobDependencyServiceInterface';

    const REPOSITORY_LOCAL = 1;

    const REPOSITORY_CHRONOS = 2;

    /**
     * @param string $sJobName
     * @param int $iRepository
     * @return string[]
     */
    public function getChildJobs($sJobName, $iRepository);

    /**
     * @param string $sJobName
     * @param int $iRepository
     * @return bool
     */
    public function hasChildJobs($sJobName, $iRepository);
}