<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */
namespace Chapi\BusinessCase\Comparison;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

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
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityA
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB);

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName);
}