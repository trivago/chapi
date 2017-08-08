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
     * @param string $jobName
     * @return array
     */
    public function getJobDiff($jobName);

    /**
     * @param JobEntityInterface $jobEntityA
     * @param JobEntityInterface $jobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB);

    /**
     * @param $jobName
     * @return bool
     */
    public function isJobAvailable($jobName);
}
