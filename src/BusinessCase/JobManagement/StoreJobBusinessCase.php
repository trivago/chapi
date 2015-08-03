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
use Chapi\Entity\Chronos\JobEntity;
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

    /**
     * @param array $aJobNames
     * @param bool|false $bForceOverwrite
     */
    public function storeJobsToLocalRepository(array $aJobNames = [], $bForceOverwrite = false)
    {
        if (empty($aJobNames))
        {
            $_aChronosJobs = $this->oJobRepositoryChronos->getJobs();
        }
        else
        {
            $_aChronosJobs = [];
            foreach ($aJobNames as $_sJobName)
            {
                $_aChronosJobs[] = $this->oJobRepositoryChronos->getJob($_sJobName);
            }
        }

        /** @var JobEntity $_oJobEntity */
        foreach ($_aChronosJobs as $_oJobEntity)
        {
            $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($_oJobEntity->name);
            // new job
            if (empty($_oJobEntityLocal->name))
            {
                $this->oJobRepositoryLocal->addJob($_oJobEntity);
                continue;
            }

            // update job
            $_aDiff = $this->oJobComparisonBusinessCase->getJobDiff($_oJobEntity->name);
            if(!empty($_aDiff))
            {
                if (!$bForceOverwrite)
                {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The job "%s" already exist in your local repository. Use the "force" option to overwrite the job',
                            $_oJobEntity->name
                        )
                    );
                }

                $this->oJobRepositoryLocal->updateJob($_oJobEntity);

                // remove job from index in case off added in the past
                $this->oJobIndexService->removeJob($_oJobEntity->name);
            }
        }
    }
}