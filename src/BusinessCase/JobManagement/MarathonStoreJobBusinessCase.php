<?php
/**
 *
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-03
 *
 */

namespace Chapi\BusinessCase\JobManagement;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class MarathonStoreJobBusinessCase extends AbstractStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * MarathonStoreJobBusinessCase constructor.
     * @param JobIndexServiceInterface $jobIndexService
     * @param JobRepositoryInterface $jobRepositoryRemote
     * @param JobRepositoryInterface $jobRepositoryLocal
     * @param JobComparisonInterface $jobComparisonBusinessCase
     * @param LoggerInterface $logger
     */
    public function __construct(
        JobIndexServiceInterface $jobIndexService,
        JobRepositoryInterface $jobRepositoryRemote,
        JobRepositoryInterface $jobRepositoryLocal,
        JobComparisonInterface $jobComparisonBusinessCase,
        LoggerInterface $logger
    ) {
        $this->jobIndexService = $jobIndexService;
        $this->logger = $logger;
        $this->jobComparisonBusinessCase = $jobComparisonBusinessCase;
        $this->jobRepositoryRemote = $jobRepositoryRemote;
        $this->jobRepositoryLocal = $jobRepositoryLocal;
    }

    /**
     * @return void
     */
    public function storeIndexedJobs()
    {
        $remoteMissingApps = $this->jobComparisonBusinessCase->getRemoteMissingJobs();
        foreach ($remoteMissingApps as $appId) {
            $this->addRemoteMissingApp($appId);
        }

        $localMissingApps = $this->jobComparisonBusinessCase->getLocalMissingJobs();
        foreach ($localMissingApps as $appId) {
            $this->removeLocalMissingAppInRemote($appId);
        }
        $localUpdates = $this->jobComparisonBusinessCase->getLocalJobUpdates();
        foreach ($localUpdates as $appId) {
            $this->updateAppInRemote($appId);
        }
    }

    /**
     * @param string $appId
     * @return bool
     */
    private function addRemoteMissingApp($appId)
    {
        if ($this->jobIndexService->isJobInIndex($appId)) {
            /** @var MarathonAppEntity $jobEntityLocal */
            $jobEntityLocal = $this->jobRepositoryLocal->getJob($appId);

            if (!$jobEntityLocal instanceof MarathonAppEntity) {
                throw new \RuntimeException('Encountered entity that is not MarathonAppEntity');
            }

            // check if dependency is satisfied
            if ($jobEntityLocal->isDependencyJob()) {
                try {
                    $circular = $this->isDependencyCircular($jobEntityLocal, count($jobEntityLocal->dependencies));
                    if ($circular) {
                        $this->logger->error(sprintf(
                            'The dependency for %s is circular. Please fix them.',
                            $appId
                        ));
                        return false;
                    }
                } catch (\Exception $exception) {
                    $this->logger->error(sprintf(
                        'Job %s cannot be added to remote : %s',
                        $appId,
                        $exception->getMessage()
                    ));
                    return false;
                }


                foreach ($jobEntityLocal->dependencies as $dependencyKey) {
                    $wasAdded = $this->addRemoteMissingApp($dependencyKey);

                    if (!$wasAdded) {
                        $this->logger->error(sprintf(
                            'Job "%s" is dependent on "%s" which is missing. Please add them and try again.',
                            $appId,
                            $dependencyKey
                        ));
                        $this->jobIndexService->removeJob($dependencyKey);
                        return false;
                    }
                }
            }

            if ($this->jobRepositoryRemote->addJob($jobEntityLocal)) {
                $this->jobIndexService->removeJob($jobEntityLocal->getKey());
                $this->logger->notice(sprintf(
                    'Job "%s" successfully added to marathon',
                    $jobEntityLocal->getKey()
                ));

                return true;
            }
            $this->logger->error(sprintf(
                'Failed to add job "%s" to marathon',
                $jobEntityLocal->getKey()
            ));
        }
        return false;
    }

    /**
     * @param array $array
     * @return bool
     */
    private function hasDuplicates($array)
    {
        return !(count($array) == count(array_unique($array)));
    }

    /**
     * @param MarathonAppEntity $entity
     * @param int $immediateChildren
     * @param array $path
     * @return bool
     * @throws \Exception
     */
    private function isDependencyCircular(MarathonAppEntity $entity, $immediateChildren, &$path = [])
    {
        // Invariant: path will not have duplicates for acyclic dependency tree
        if ($this->hasDuplicates($path)) {
            return true;
        }

        // if we hit leaf (emptyarray), and have no
        // cycle yet, then remove the leaf and return false
        // removing leaf will help maintain a proper path from root to leaf
        // For tree : A ---> B ---> D
        //                      |-> C
        // When we reach node D, path will be [A, B, D]
        // so we pop off D so that the next append will properly show [A, B, C] (legit path)
        if (empty($entity->dependencies)) {
            array_pop($path);
            return false;
        }

        foreach ($entity->dependencies as $dependency) {
            // add this key in path as we will explore its child now
            $path[] = $entity->getKey();

            /** @var MarathonAppEntity $dependEntity */
            $dependEntity = $this->jobRepositoryLocal->getJob($dependency);

            if (!$dependEntity) {
                throw new \Exception(sprintf('Dependency chain on non-existing app "%s"', $dependency));
            }

            if (!$dependEntity instanceof MarathonAppEntity) {
                throw new \RuntimeException('Expected MarathonAppEntity. Found something else');
            }


            // check if dependency has cycle
            if ($this->isDependencyCircular($dependEntity, count($dependEntity->dependencies), $path)) {
                return true;
            }

            // tracking immediateChildren, this helps us with
            // removing knowing when to pop off key for intermediary dependency
            // For tree: A ---> B ---> D
            //              |      |-> C
            //              |->E
            // for B intermediate Child will be 2.
            // when we process D, it will be reduced to 1 and with C to 0
            // then we will pop B to generate path [A, E] when we reach E.
            $immediateChildren--;
            if ($immediateChildren == 0) {
                array_pop($path);
            }
        }

        return false;
    }

    /**
     * @param string $appId
     * @return bool
     */
    private function removeLocalMissingAppInRemote($appId)
    {
        if ($this->jobIndexService->isJobInIndex($appId)) {
            if ($this->jobRepositoryRemote->removeJob($appId)) {
                $this->jobIndexService->removeJob($appId);
                $this->logger->notice(sprintf(
                    'Job "%s" successfully removed from marathon',
                    $appId
                ));

                return true;
            }
            $this->logger->error(sprintf(
                'Failed to remove"%s" from marathon',
                $appId
            ));
        }
        return false;
    }

    /**
     * @param string $appId
     * @return bool
     */
    private function updateAppInRemote($appId)
    {
        if ($this->jobIndexService->isJobInIndex($appId)) {
            $updatedConfig = $this->jobRepositoryLocal->getJob($appId);
            $wasAddedBack = $this->jobRepositoryRemote->updateJob($updatedConfig);

            // updated
            if ($wasAddedBack) {
                $this->jobIndexService->removeJob($appId);
                $this->logger->notice(sprintf(
                    'Job "%s" successfully updated in marathon',
                    $appId
                ));

                return true;
            }

            $this->logger->error(sprintf(
                'Failed to update job "%s" in marathon',
                $appId
            ));
        }

        return false;
    }
}
