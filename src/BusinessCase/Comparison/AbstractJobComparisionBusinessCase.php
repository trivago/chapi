<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 15/02/17
 * Time: 15:01
 */

namespace Chapi\BusinessCase\Comparison;

use Chapi\Component\Comparison\DiffCompareInterface;
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;

abstract class AbstractJobComparisionBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryInterface
     */
    protected $remoteRepository;
    /**
     * @var JobRepositoryInterface
     */
    protected $localRepository;
    /**
     * @var DiffCompareInterface
     */
    protected $diffCompare;

    /**
     * @inheritdoc
     */
    public function getLocalMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->localRepository->getJobs(),
            $this->remoteRepository->getJobs()
        );
    }

    /**
     * @inheritdoc
     */
    public function getRemoteMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->remoteRepository->getJobs(),
            $this->localRepository->getJobs()
        );
    }

    /**
     * @inheritdoc
     */
    public function isJobAvailable($jobName)
    {
        $locallyAvailable = $this->localRepository->getJob($jobName);
        $remotelyAvailable = $this->remoteRepository->getJob($jobName);
        return $locallyAvailable || $remotelyAvailable;
    }


    /**
     * @param JobCollection $jobCollectionA
     * @param JobCollection $jobCollectionB
     * @return array<string>
     */
    protected function getMissingJobsInCollectionA(JobCollection $jobCollectionA, JobCollection $jobCollectionB)
    {
        return array_diff(
            array_keys($jobCollectionB->getArrayCopy()),
            array_keys($jobCollectionA->getArrayCopy())
        );
    }

    protected function getDifference(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        $jobACopy = [];
        $jobBCopy = [];

        if ($jobEntityA) {
            $jobACopy = $jobEntityA->getSimpleArrayCopy();
        }

        if ($jobEntityB) {
            $jobBCopy = $jobEntityB->getSimpleArrayCopy();
        }

        return array_merge(
            array_diff_assoc(
                $jobACopy,
                $jobBCopy
            ),
            array_diff_assoc(
                $jobBCopy,
                $jobACopy
            )
        );
    }


    /**
     * @param JobEntityInterface $jobEntityA
     * @param JobEntityInterface $jobEntityB
     * @return array
     */
    protected function compareJobEntities(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        $nonidenticalProperties = [];

        $differences = $this->getDifference($jobEntityA, $jobEntityB);

        if (count($differences) > 0) {
            $diffKeys = array_keys($differences);

            foreach ($diffKeys as $diffKey) {
                if (!$this->isEntityEqual($diffKey, $jobEntityA, $jobEntityB)) {
                    $nonidenticalProperties[] = $diffKey;
                }
            }
        }

        return $nonidenticalProperties;
    }


    /**
     * @inheritdoc
     */
    public function getJobDiff($jobName)
    {
        $differences = [];
        $localEntity = $this->localRepository->getJob($jobName);
        $remoteEntity = $this->remoteRepository->getJob($jobName);

        if (!$localEntity && !$remoteEntity) {
            // return as jobs doesnt exist
            return [];
        }

        if (!$localEntity) {
            $localEntity = $this->getEntitySetWithDefaults();
        }

        if (!$remoteEntity) {
            $remoteEntity = $this->getEntitySetWithDefaults();
        }

        $this->preCompareModifications($localEntity, $remoteEntity);
        $nonidenticalProperties = $this->compareJobEntities(
            $localEntity,
            $remoteEntity
        );

        foreach ($nonidenticalProperties as $property) {
            $differences[$property] = $this->diffCompare->compare(
                $remoteEntity->{$property},
                $localEntity->{$property}
            );
        }

        return $differences;
    }



    /**
     * @inheritdoc
     */
    public function getLocalJobUpdates()
    {
        $locallyUpdatedJobs = [];
        $localJobs = $this->localRepository->getJobs();

        /** @var JobEntityInterface $localJob */
        foreach ($localJobs as $localJob) {

            /** @var JobEntityInterface $remoteJob */
            $remoteJob = $this->remoteRepository->getJob($localJob->getKey());
            if (!$remoteJob) {
                // if doesn't exist in remote, its not update. its new
                continue;
            }

            $this->preCompareModifications($localJob, $remoteJob);

            $nonidenticalProperties = $this->compareJobEntities($localJob, $remoteJob);

            if (!empty($nonidenticalProperties)) {
                $locallyUpdatedJobs[] = $localJob->getKey();
            }
        }
        return $locallyUpdatedJobs;
    }


    /**
     * This method should perform any operation that is desired before comparing remote and local entities.
     * Why this is required?
     * For system like marathon, it is essential to set/unset certain values before comparing to make sane
     * comparision.
     *
     * Note: Should be careful with the parameters as they are passed by value.
     *
     * @param JobEntityInterface $localJob
     * @param JobEntityInterface $remoteJob
     * @return null
     */
    abstract protected function preCompareModifications(JobEntityInterface &$localJob, JobEntityInterface &$remoteJob);

    /**
     * Gets entity for each system with defaults set
     * @return JobEntityInterface
     */
    abstract protected function getEntitySetWithDefaults();

    /**
     * Verify if two entities are equal.
     *
     * @param $property
     * @param JobEntityInterface $jobEntityA
     * @param JobEntityInterface $jobEntityB
     * @return mixed
     */
    abstract protected function isEntityEqual($property, JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB);

    /**
     * @param JobEntityInterface $jobEntityA
     * @param JobEntityInterface $jobEntityB
     * @return bool
     */
    abstract public function hasSameJobType(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB);
}
