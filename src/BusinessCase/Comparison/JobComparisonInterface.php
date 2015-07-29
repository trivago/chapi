<?php
/**
 * @package: orchestra-
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 * @link:    http://
 */


namespace Chapi\BusinessCase\Comparison;


interface JobComparisonInterface
{
    const DIC_NAME = 'JobComparisonInterface';

    public function getLocalMissingJobs();

    public function getChronosMissingJobs();

    public function getLocalJobUpdates();
}