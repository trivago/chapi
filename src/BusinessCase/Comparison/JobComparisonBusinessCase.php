<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */

namespace Chapi\BusinessCase\Comparison;

use Chapi\Service\JobRepository\JobRepositoryServiceInterface;

class JobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryServiceInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobRepositoryServiceInterface
     */
    private $oJobRepositoryChronos;

    /**
     * @param JobRepositoryServiceInterface $oJobRepositoryLocal
     * @param JobRepositoryServiceInterface $oJobRepositoryChronos
     */
    public function __construct(
        JobRepositoryServiceInterface $oJobRepositoryLocal,
        JobRepositoryServiceInterface $oJobRepositoryChronos
    )
    {
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
    }

    public function getLocalMissingJobs()
    {
        $_aJobsLocal = $this->oJobRepositoryLocal->getJobs()->getArrayCopy();
        $_aJobsChronos = $this->oJobRepositoryChronos->getJobs()->getArrayCopy();

        // missing jobs
        $_aMissingJobs = array_diff(
            array_keys($_aJobsChronos),
            array_keys($_aJobsLocal)
        );

        return $_aMissingJobs;
    }

    public function getChronosMissingJobs()
    {
        $_aJobsLocal = $this->oJobRepositoryLocal->getJobs()->getArrayCopy();
        $_aJobsChronos = $this->oJobRepositoryChronos->getJobs()->getArrayCopy();

        $_aJobs = array_diff(
            array_keys($_aJobsLocal),
            array_keys($_aJobsChronos)
        );

        return $_aJobs;
    }

    public function getLocalJobUpdates()
    {
        // TODO: Implement getLocalJobUpdates() method.
    }
}