<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 15/02/17
 * Time: 13:17
 */

namespace Chapi\BusinessCase\JobManagement;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractStoreJobBusinessCase implements StoreJobBusinessCaseInterface
{
    /**
     * @var JobIndexServiceInterface
     */
    protected $jobIndexService;


    /**
     * @var JobComparisonInterface
     */
    protected $jobComparisonBusinessCase;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JobRepositoryInterface
     */
    protected $jobRepositoryRemote;

    /**
     * @var JobRepositoryInterface
     */
    protected $jobRepositoryLocal;

    /**
     * @inheritdoc
     */
    public function storeJobsToLocalRepository(array $entityNames = [], $forceOverwrite = false)
    {
        if (empty($entityNames)) {
            $remoteEntities = $this->jobRepositoryRemote->getJobs();
        } else {
            $remoteEntities = [];
            foreach ($entityNames as $jobName) {
                $remoteEntities[] = $this->jobRepositoryRemote->getJob($jobName);
            }
        }

        /** @var JobEntityInterface $remoteEntity */
        foreach ($remoteEntities as $remoteEntity) {
            $localEntity = $this->jobRepositoryLocal->getJob($remoteEntity->getKey());
            // new job
            if (null == $localEntity) {
                $this->addJobInLocalRepository($remoteEntity);
            } else {
                //update
                $this->updateJobInLocalRepository($remoteEntity, $forceOverwrite);
            }
        }
    }

    protected function addJobInLocalRepository(JobEntityInterface $appRemote)
    {
        if ($this->jobRepositoryLocal->addJob($appRemote)) {
            $this->logger->notice(sprintf(
                'Entity %s stored in local repository',
                $appRemote->getKey()
            ));
        } else {
            $this->logger->error(sprintf(
                'Failed to store %s in local repository',
                $appRemote->getKey()
            ));
        }
    }


    protected function updateJobInLocalRepository(JobEntityInterface $appRemote, $forceOverwrite)
    {
        $diff = $this->jobComparisonBusinessCase->getJobDiff($appRemote->getKey());
        if (!empty($diff)) {
            if (!$forceOverwrite) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The entity "%s" already exist in your local repository. Use the "force" option to overwrite the job',
                        $appRemote->getKey()
                    )
                );
            }

            if ($this->jobRepositoryLocal->updateJob($appRemote)) {
                $this->logger->notice(sprintf(
                    'Entity %s is updated in local repository',
                    $appRemote->getKey()
                ));
            } else {
                $this->logger->error(sprintf(
                    'Failed to update app %s in local repository',
                    $appRemote->getKey()
                ));
            }

            // remove job from index in case off added in the past
            $this->jobIndexService->removeJob($appRemote->getKey());
        }
    }

    /**
     * @inheritdoc
     */
    public function isJobAvailable($jobName)
    {
        $locallyAvailable = $this->jobRepositoryLocal->getJob($jobName) ? true : false;
        $remotelyAvailable = $this->jobRepositoryRemote->getJob($jobName) ? true : false;
        return $locallyAvailable || $remotelyAvailable;
    }


    /**
     * @inheritdoc
     */
    abstract public function storeIndexedJobs();
}
