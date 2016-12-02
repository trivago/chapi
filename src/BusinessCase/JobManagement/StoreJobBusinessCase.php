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
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
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
     * @var JobDependencyServiceInterface
     */
    private $oJobDependencyService;

    /**
     * @var LoggerInterface
     */
    private $oLogger;


    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryInterface $oJobRepositoryChronos,
        JobRepositoryInterface $oJobRepositoryLocal,
        JobComparisonInterface  $oJobComparisonBusinessCase,
        JobDependencyServiceInterface $oJobDependencyService,
        LoggerInterface $oLogger
    )
    {
        $this->oJobIndexService = $oJobIndexService;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobComparisonBusinessCase = $oJobComparisonBusinessCase;
        $this->oJobDependencyService = $oJobDependencyService;
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
            $this->hasAddedJob($_sJobName);
        }

        // delete missing jobs from chronos
        $_aMissingJobs = $this->oJobComparisonBusinessCase->getLocalMissingJobs();
        foreach ($_aMissingJobs as $_sJobName)
        {
            $this->hasRemovedJob($_sJobName);
        }

        // update jobs on chronos
        $_aLocalJobUpdates = $this->oJobComparisonBusinessCase->getLocalJobUpdates();
        foreach ($_aLocalJobUpdates as $_sJobName)
        {
            $this->hasUpdatedJob($_sJobName);
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

        /** @var ChronosJobEntity $_oJobEntity */
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
            if (!empty($_aDiff))
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

    /**
     * @param string $sJobName
     * @return bool
     */
    private function hasAddedJob($sJobName)
    {
        $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sJobName);

        if ($this->isAbleToStoreEntity($_oJobEntityLocal))
        {
            if ($this->oJobRepositoryChronos->addJob($_oJobEntityLocal))
            {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->name);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully added to chronos',
                    $_oJobEntityLocal->name
                ));

                return true;
            }

            $this->oLogger->error(sprintf(
                'Failed to add job "%s" to chronos',
                $_oJobEntityLocal->name
            ));
        }

        return false;
    }

    /**
     * @param $sJobName
     * @return bool
     */
    private function hasRemovedJob($sJobName)
    {
        if ($this->isAbleToDeleteJob($sJobName))
        {
            if ($this->oJobRepositoryChronos->removeJob($sJobName))
            {
                $this->oJobIndexService->removeJob($sJobName);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully removed from chronos',
                    $sJobName
                ));

                return true;
            }

            $this->oLogger->error(sprintf(
                'Failed to remove job "%s" from chronos',
                $sJobName
            ));
        }

        return false;
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    private function hasUpdatedJob($sJobName)
    {
        $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sJobName);

        if ($this->isAbleToStoreEntity($_oJobEntityLocal))
        {
            $_oJobEntityChronos = $this->oJobRepositoryChronos->getJob($sJobName);

            // handle job update
            if ($this->oJobComparisonBusinessCase->hasSameJobType($_oJobEntityLocal, $_oJobEntityChronos))
            {
                $_bHasUpdatedJob = $this->oJobRepositoryChronos->updateJob($_oJobEntityLocal);
            }
            else
            {
                $_bHasUpdatedJob = (
                    $this->oJobRepositoryChronos->removeJob($_oJobEntityChronos->name)
                    && $this->oJobRepositoryChronos->addJob($_oJobEntityLocal)
                );
            }

            // handle update result
            if ($_bHasUpdatedJob)
            {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->name);
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully updated in chronos',
                    $_oJobEntityLocal->name
                ));

                return true;
            }

            // in case of an error
            $this->oLogger->error(sprintf(
                'Failed to update job "%s" in chronos',
                $_oJobEntityLocal->name
            ));
        }

        return false;
    }

    /**
     * @param ChronosJobEntity $oEntity
     * @return bool
     */
    private function isAbleToStoreEntity(ChronosJobEntity $oEntity)
    {
        if ($this->oJobIndexService->isJobInIndex($oEntity->name))
        {
            if ($oEntity->isSchedulingJob())
            {
                return true;
            }

            //else :: are all parents available?
            foreach ($oEntity->parents as $_sParentJobName)
            {
                if (false === $this->oJobRepositoryChronos->hasJob($_sParentJobName))
                {
                    $this->oLogger->warning(sprintf(
                        'Parent job is not available for "%s" on chronos. Please add parent "%s" first.',
                        $oEntity->name,
                        $_sParentJobName
                    ));

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    private function isAbleToDeleteJob($sJobName)
    {
        if ($this->oJobIndexService->isJobInIndex($sJobName))
        {
            $_aChildJobs = $this->oJobDependencyService->getChildJobs($sJobName, JobDependencyServiceInterface::REPOSITORY_CHRONOS);
            if (empty($_aChildJobs))
            {
                return true;
            }

            // else :: are child also in index to delete?
            foreach ($_aChildJobs as $_sChildJobName)
            {
                if (false === $this->oJobIndexService->isJobInIndex($_sChildJobName))
                {
                    $this->oLogger->warning(sprintf(
                        'Child job is still available for "%s" on chronos. Please remove child "%s" first.',
                        $sJobName,
                        $_sChildJobName
                    ));

                    return false;
                }
            }

            // child job are also in index
            return true;
        }

        return false;
    }
}