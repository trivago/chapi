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
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class StoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * @var JobIndexServiceInterface
     */
    private $oJobIndexService;

    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryChronos;

    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobComparisonInterface
     */
    private $oJobComparisonBusinessCase;

    /**
     * @var LoggerInterface
     */
    private $oLogger;

    /**
     * @param JobIndexServiceInterface $oJobIndexService
     * @param JobRepositoryInterface $oJobRepositoryChronos
     * @param JobRepositoryInterface $oJobRepositoryLocal
     * @param JobComparisonInterface $oJobComparisonBusinessCase
     */
    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryInterface $oJobRepositoryChronos,
        JobRepositoryInterface $oJobRepositoryLocal,
        JobComparisonInterface  $oJobComparisonBusinessCase,
        LoggerInterface $oLogger
    )
    {
        $this->oJobIndexService = $oJobIndexService;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobComparisonBusinessCase = $oJobComparisonBusinessCase;
        $this->oLogger = $oLogger;
    }

    /**
     * @inheritdoc
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
                    $this->oLogger->notice(sprintf(
                        'Job "%s" successfully added to chronos',
                        $_sJobName
                    ));
                }
                else
                {
                    $this->oLogger->error(sprintf(
                        'Failed to add job "%s" to chronos',
                        $_sJobName
                    ));
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
                    $this->oLogger->notice(sprintf(
                        'Job "%s" successfully removed from chronos',
                        $_sJobName
                    ));
                }
                else
                {
                    $this->oLogger->error(sprintf(
                        'Failed to remove job "%s" from chronos',
                        $_sJobName
                    ));
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
                    $this->oLogger->notice(sprintf(
                        'Job "%s" successfully updated in chronos',
                        $_sJobName
                    ));
                }
                else
                {
                    $this->oLogger->error(sprintf(
                        'Failed to update job "%s" in chronos',
                        $_sJobName
                    ));
                }
            }
        }
    }

    /**
     * @inheritdoc
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
                if ($this->oJobRepositoryLocal->addJob($_oJobEntity))
                {
                    $this->oLogger->notice(sprintf(
                        'Job "%s" successfully stored in local repository',
                        $_oJobEntity->name
                    ));
                }
                else
                {
                    $this->oLogger->error(sprintf(
                        'Failed to store job "%s" in local repository',
                        $_oJobEntity->name
                    ));
                }

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

                if ($this->oJobRepositoryLocal->updateJob($_oJobEntity))
                {
                    $this->oLogger->notice(sprintf(
                        'Job "%s" successfully updated in local repository',
                        $_oJobEntity->name
                    ));
                }
                else
                {
                    $this->oLogger->error(sprintf(
                        'Failed to update job "%s" in local repository',
                        $_oJobEntity->name
                    ));
                }

                // remove job from index in case off added in the past
                $this->oJobIndexService->removeJob($_oJobEntity->name);
            }
        }
    }
}