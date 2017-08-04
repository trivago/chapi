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
    private $jobDependencyService;

    /**
     * @param JobIndexServiceInterface $jobIndexService
     * @param JobRepositoryInterface $jobRepositoryRemote
     * @param JobRepositoryInterface $jobRepositoryLocal
     * @param JobComparisonInterface $jobComparisonBusinessCase
     * @param JobDependencyServiceInterface $jobDependencyService
     * @param LoggerInterface $logger
     */
    public function __construct(
        JobIndexServiceInterface $jobIndexService,
        JobRepositoryInterface $jobRepositoryRemote,
        JobRepositoryInterface $jobRepositoryLocal,
        JobComparisonInterface  $jobComparisonBusinessCase,
        JobDependencyServiceInterface $jobDependencyService,
        LoggerInterface $logger
    ) {
        $this->jobIndexService = $jobIndexService;
        $this->jobRepositoryRemote = $jobRepositoryRemote;
        $this->jobRepositoryLocal = $jobRepositoryLocal;
        $this->jobComparisonBusinessCase = $jobComparisonBusinessCase;
        $this->jobDependencyService = $jobDependencyService;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function storeIndexedJobs()
    {
        // add new jobs to chronos
        $newJobs = $this->jobComparisonBusinessCase->getRemoteMissingJobs();
        foreach ($newJobs as $jobName) {
            $this->hasAddedJob($jobName);
        }

        // delete missing jobs from chronos
        $missingJobs = $this->jobComparisonBusinessCase->getLocalMissingJobs();
        foreach ($missingJobs as $jobName) {
            $this->hasRemovedJob($jobName);
        }

        // update jobs on chronos
        $localJobUpdates = $this->jobComparisonBusinessCase->getLocalJobUpdates();
        foreach ($localJobUpdates as $jobName) {
            $this->hasUpdatedJob($jobName);
        }
    }

    /**
     * @param string $jobName
     * @return bool
     */
    private function hasAddedJob($jobName)
    {
        $jobEntityLocal = $this->jobRepositoryLocal->getJob($jobName);

        if (!$jobEntityLocal instanceof ChronosJobEntity) {
            throw new \RuntimeException('Expected ChronosJobEntity. Received something else.');
        }

        if ($this->isAbleToStoreEntity($jobEntityLocal)) {
            if ($this->jobRepositoryRemote->addJob($jobEntityLocal)) {
                $this->jobIndexService->removeJob($jobEntityLocal->getKey());
                $this->logger->notice(sprintf(
                    'Job "%s" successfully added to chronos',
                    $jobEntityLocal->getKey()
                ));

                return true;
            }

            $this->logger->error(sprintf(
                'Failed to add job "%s" to chronos',
                $jobEntityLocal->getKey()
            ));
        }

        return false;
    }

    /**
     * @param $jobName
     * @return bool
     */
    private function hasRemovedJob($jobName)
    {
        if ($this->isAbleToDeleteJob($jobName)) {
            if ($this->jobRepositoryRemote->removeJob($jobName)) {
                $this->jobIndexService->removeJob($jobName);
                $this->logger->notice(sprintf(
                    'Job "%s" successfully removed from chronos',
                    $jobName
                ));

                return true;
            }

            $this->logger->error(sprintf(
                'Failed to remove job "%s" from chronos',
                $jobName
            ));
        }

        return false;
    }

    /**
     * @param string $jobName
     * @return bool
     */
    private function hasUpdatedJob($jobName)
    {
        $jobEntityLocal = $this->jobRepositoryLocal->getJob($jobName);

        if (!$jobEntityLocal instanceof ChronosJobEntity) {
            throw new \RuntimeException('Expected ChronosJobEntity. Received something else.');
        }

        if ($this->isAbleToStoreEntity($jobEntityLocal)) {
            $jobEntityChronos = $this->jobRepositoryRemote->getJob($jobName);

            // handle job update
            if ($this->jobComparisonBusinessCase->hasSameJobType($jobEntityLocal, $jobEntityChronos)) {
                $hasUpdatedJob = $this->jobRepositoryRemote->updateJob($jobEntityLocal);
            } else {
                $hasUpdatedJob = (
                    $this->jobRepositoryRemote->removeJob($jobEntityChronos->getKey())
                    && $this->jobRepositoryRemote->addJob($jobEntityLocal)
                );
            }

            // handle update result
            if ($hasUpdatedJob) {
                $this->jobIndexService->removeJob($jobEntityLocal->getKey());
                $this->logger->notice(sprintf(
                    'Job "%s" successfully updated in chronos',
                    $jobEntityLocal->getKey()
                ));

                return true;
            }

            // in case of an error
            $this->logger->error(sprintf(
                'Failed to update job "%s" in chronos',
                $jobEntityLocal->getKey()
            ));
        }

        return false;
    }

    /**
     * @param ChronosJobEntity $entity
     * @return bool
     */
    private function isAbleToStoreEntity(ChronosJobEntity $entity)
    {
        if ($this->jobIndexService->isJobInIndex($entity->getKey())) {
            if ($entity->isSchedulingJob()) {
                return true;
            }

            //else :: are all parents available?
            foreach ($entity->parents as $parentJobName) {
                if (false === $this->jobRepositoryRemote->hasJob($parentJobName)) {
                    $this->logger->warning(sprintf(
                        'Parent job is not available for "%s" on chronos. Please add parent "%s" first.',
                        $entity->name,
                        $parentJobName
                    ));

                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $jobName
     * @return bool
     */
    private function isAbleToDeleteJob($jobName)
    {
        if ($this->jobIndexService->isJobInIndex($jobName)) {
            $childJobs = $this->jobDependencyService->getChildJobs($jobName, JobDependencyServiceInterface::REPOSITORY_CHRONOS);
            if (empty($childJobs)) {
                return true;
            }

            // else :: are child also in index to delete?
            foreach ($childJobs as $childJobName) {
                if (false === $this->jobIndexService->isJobInIndex($childJobName)) {
                    $this->logger->warning(sprintf(
                        'Child job is still available for "%s" on chronos. Please remove child "%s" first.',
                        $jobName,
                        $childJobName
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
