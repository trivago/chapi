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
    const DIC_NAME          = 'JobComparisonInterface';
    const DIC_NAME_CHRONOS  = 'ChronosJobComparisionBusinessCase';
    const DIC_NAME_MARATHON = 'MarathonJobComparisionBusinessCase';

    /**
     * @return array
     */
    public function getLocalMissingJobs();

    /**
     * @return array
     */
    public function getRemoteMissingJobs();

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
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB);

    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName);
}
