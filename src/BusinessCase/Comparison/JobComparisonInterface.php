<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */
namespace Chapi\BusinessCase\Comparison;


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
}