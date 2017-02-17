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

class ChronosStoreJobBusinessCase extends AbstractStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{

    /**
     * @var JobDependencyServiceInterface
     */
    private $oJobDependencyService;


    public function __construct(
        JobIndexServiceInterface $oJobIndexService,
        JobRepositoryInterface $oJobRepositoryRemote,
        JobRepositoryInterface $oJobRepositoryLocal,
        JobComparisonInterface  $oJobComparisonBusinessCase,
        JobDependencyServiceInterface $oJobDependencyService,
        LoggerInterface $oLogger
    )
    {
        $this->oJobIndexService = $oJobIndexService;
        $this->oJobRepositoryRemote = $oJobRepositoryRemote;
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
        $_aNewJobs = $this->oJobComparisonBusinessCase->getRemoteMissingJobs();
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
     * @param string $sJobName
     * @return bool
     */
    private function hasAddedJob($sJobName)
    {
        $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sJobName);

        if (!$_oJobEntityLocal instanceof ChronosJobEntity)
        {
            throw new \RuntimeException('Expected ChronosJobEntity. Received something else.');
        }

        if ($this->isAbleToStoreEntity($_oJobEntityLocal))
        {
            if ($this->oJobRepositoryRemote->addJob($_oJobEntityLocal))
            {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->getKey());
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully added to chronos',
                    $_oJobEntityLocal->getKey()
                ));

                return true;
            }

            $this->oLogger->error(sprintf(
                'Failed to add job "%s" to chronos',
                $_oJobEntityLocal->getKey()
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
            if ($this->oJobRepositoryRemote->removeJob($sJobName))
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

        if (!$_oJobEntityLocal instanceof ChronosJobEntity)
        {
            throw new \RuntimeException('Expected ChronosJobEntity. Received something else.');
        }

        if ($this->isAbleToStoreEntity($_oJobEntityLocal))
        {
            $_oJobEntityChronos = $this->oJobRepositoryRemote->getJob($sJobName);

            // handle job update
            if ($this->oJobComparisonBusinessCase->hasSameJobType($_oJobEntityLocal, $_oJobEntityChronos))
            {
                $_bHasUpdatedJob = $this->oJobRepositoryRemote->updateJob($_oJobEntityLocal);
            }
            else
            {
                $_bHasUpdatedJob = (
                    $this->oJobRepositoryRemote->removeJob($_oJobEntityChronos->getKey())
                    && $this->oJobRepositoryRemote->addJob($_oJobEntityLocal)
                );
            }

            // handle update result
            if ($_bHasUpdatedJob)
            {
                $this->oJobIndexService->removeJob($_oJobEntityLocal->getKey());
                $this->oLogger->notice(sprintf(
                    'Job "%s" successfully updated in chronos',
                    $_oJobEntityLocal->getKey()
                ));

                return true;
            }

            // in case of an error
            $this->oLogger->error(sprintf(
                'Failed to update job "%s" in chronos',
                $_oJobEntityLocal->getKey()
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
        if ($this->oJobIndexService->isJobInIndex($oEntity->getKey()))
        {
            if ($oEntity->isSchedulingJob())
            {
                return true;
            }

            //else :: are all parents available?
            foreach ($oEntity->parents as $_sParentJobName)
            {
                if (false === $this->oJobRepositoryRemote->hasJob($_sParentJobName))
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