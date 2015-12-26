<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */
namespace Chapi\BusinessCase\Comparison;

use Chapi\Entity\Chronos\JobEntity;

interface JobComparisonInterface
{
    const DIC_NAME = 'JobComparisonInterface';

    /**
     * @return array
     */
    public function getLocalMissingJobs();

    /**
     * @return array
     */
    public function getChronosMissingJobs();

    /**
     * @return array
     */
    public function getLocalJobUpdates();

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobDiff($sJobName);

    /**
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntity $oJobEntityA, JobEntity $oJobEntityB);
}