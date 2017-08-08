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
     * @param string $jobName
     * @param int $repository
     * @return string[]
     */
    public function getChildJobs($jobName, $repository);

    /**
     * @param string $jobName
     * @param int $repository
     * @return bool
     */
    public function hasChildJobs($jobName, $repository);
}
