<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\BusinessCase\JobManagement;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryServiceInterface;

class StoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * @var JobIndexServiceInterface
     */
    private $oJobIndexService;

    /**
     * @var JobRepositoryServiceInterface
     */
    private $oJobRepositoryChronos;

    /**
     * @var JobRepositoryServiceInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobComparisonInterface
     */
    private $oJobComparisonBusinessCase;

    /**
     * @param JobIndexServiceInterface $oJobIndexService
     * @param JobRepositoryServiceInterface $oJobRepositoryChronos
     * @param JobRepositoryServiceInterface $oJobRepositoryLocal
     * @param JobComparisonInterface $oJobComparisonBusinessCase
     */
    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryServiceInterface $oJobRepositoryChronos,
        JobRepositoryServiceInterface $oJobRepositoryLocal,
        JobComparisonInterface  $oJobComparisonBusinessCase
    )
    {
        $this->oJobIndexService = $oJobIndexService;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobComparisonBusinessCase = $oJobComparisonBusinessCase;
    }

    /**
     *
     */
    public function storeIndexedJobs()
    {
        // add new jobs to chronos
        $_aNewJobs = $this->oJobComparisonBusinessCase->getChronosMissingJobs();
        foreach ($_aNewJobs as $_sJobName)
        {
            if ($this->oJobIndexService->isJobInIndex($_sJobName))
            {
                $_oJobEntity = $this->oJobRepositoryLocal->getJob($_sJobName);

                if ($this->oJobRepositoryChronos->addJob($_oJobEntity))
                {
                    $this->oJobIndexService->removeJob($_sJobName);
                    //todo: log "added $_sJobName successfully in chronos\n";
                }
            }
        }

        // delete missing jobs from chronos
        $_aMissingJobs = $this->oJobComparisonBusinessCase->getLocalMissingJobs();
        foreach ($_aMissingJobs as $_sJobName)
        {
            if ($this->oJobIndexService->isJobInIndex($_sJobName))
            {
                if ($this->oJobRepositoryChronos->removeJob($_sJobName))
                {
                    $this->oJobIndexService->removeJob($_sJobName);
                    //todo: log "removed $_sJobName successfully in chronos\n";
                }
            }
        }

        // update jobs on chronos
        $_aLocalJobUpdates = $this->oJobComparisonBusinessCase->getLocalJobUpdates();
        foreach ($_aLocalJobUpdates as $_sJobName)
        {
            if ($this->oJobIndexService->isJobInIndex($_sJobName))
            {
                $_oJobEntity = $this->oJobRepositoryLocal->getJob($_sJobName);
                if ($this->oJobRepositoryChronos->updateJob($_oJobEntity))
                {
                    $this->oJobIndexService->removeJob($_sJobName);
                    //todo: log "updated $_sJobName successfully in chronos\n";
                }
            }
        }
    }
}