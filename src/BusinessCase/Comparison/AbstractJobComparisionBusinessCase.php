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
    protected $oRemoteRepository;
    /**
     * @var JobRepositoryInterface
     */
    protected $oLocalRepository;
    /**
     * @var DiffCompareInterface
     */
    protected $oDiffCompare;

    /**
     * @inheritdoc
     */
    public function getLocalMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oLocalRepository->getJobs(),
            $this->oRemoteRepository->getJobs()
        );
    }

    /**
     * @inheritdoc
     */
    public function getRemoteMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oRemoteRepository->getJobs(),
            $this->oLocalRepository->getJobs()
        );
    }

    /**
     * @inheritdoc
     */
    public function isJobAvailable($sJobName)
    {
        $_bLocallyAvailable = $this->oLocalRepository->getJob($sJobName);
        $_bRemotelyAvailable = $this->oRemoteRepository->getJob($sJobName);
        return $_bLocallyAvailable || $_bRemotelyAvailable;
    }


    /**
     * @param JobCollection $oJobCollectionA
     * @param JobCollection $oJobCollectionB
     * @return array<string>
     */
    protected function getMissingJobsInCollectionA(JobCollection $oJobCollectionA, JobCollection $oJobCollectionB)
    {
        return array_diff(
            array_keys($oJobCollectionB->getArrayCopy()),
            array_keys($oJobCollectionA->getArrayCopy())
        );
    }

    protected function getDifference(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        $aJobACopy = [];
        $aJobBCopy = [];

        if ($oJobEntityA)
        {
            $aJobACopy = $oJobEntityA->getSimpleArrayCopy();
        }

        if ($oJobEntityB)
        {
            $aJobBCopy = $oJobEntityB->getSimpleArrayCopy();
        }

        return array_merge(
            array_diff_assoc(
                $aJobACopy,
                $aJobBCopy
            ),
            array_diff_assoc(
                $aJobBCopy,
                $aJobACopy
            )
        );
    }


    /**
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return array
     */
    protected function compareJobEntities(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        $_aNonidenticalProperties = [];

        $_aDiff = $this->getDifference($oJobEntityA, $oJobEntityB);

        if (count($_aDiff) > 0)
        {
            $_aDiffKeys = array_keys($_aDiff);

            foreach ($_aDiffKeys as $_sDiffKey)
            {
                if (!$this->isEntityEqual($_sDiffKey, $oJobEntityA, $oJobEntityB))
                {
                    $_aNonidenticalProperties[] = $_sDiffKey;
                }
            }
        }

        return $_aNonidenticalProperties;
    }


    /**
     * @inheritdoc
     */
    public function getJobDiff($sJobName)
    {
        $_aDifferences = [];
        $_oLocalEntity = $this->oLocalRepository->getJob($sJobName);
        $_oRemoteEntity = $this->oRemoteRepository->getJob($sJobName);

        if (!$_oLocalEntity && !$_oRemoteEntity)
        {
            // return as jobs doesnt exist
            return [];
        }

        if (!$_oLocalEntity)
        {
            $_oLocalEntity = $this->getEntitySetWithDefaults();
        }

        if (!$_oRemoteEntity)
        {
            $_oRemoteEntity = $this->getEntitySetWithDefaults();
        }

        $_aNonIdenticalProps = $this->compareJobEntities(
            $_oLocalEntity,
            $_oRemoteEntity
        );

        foreach ($_aNonIdenticalProps as $_sProperty)
        {
            $_aDifferences[$_sProperty] = $this->oDiffCompare->compare(
                $_oRemoteEntity->{$_sProperty},
                $_oLocalEntity->{$_sProperty}
            );
        }

        return $_aDifferences;
    }



    /**
     * @inheritdoc
     */
    public function getLocalJobUpdates()
    {
        $_aLocallyUpdatedJobs = [];
        $_aLocalJobs = $this->oLocalRepository->getJobs();

        /** @var JobEntityInterface $_oLocalJob */
        foreach ($_aLocalJobs as $_oLocalJob)
        {

            /** @var JobEntityInterface $_oRemoteJob */
            $_oRemoteJob = $this->oRemoteRepository->getJob($_oLocalJob->getKey());
            if (!$_oRemoteJob)
            {
                // if doesn't exist in remote, its not update. its new
                continue;
            }

            $this->preCompareModifications($_oLocalJob, $_oRemoteJob);

            $_aNonidenticalProperties = $this->compareJobEntities($_oLocalJob, $_oRemoteJob);

            if (!empty($_aNonidenticalProperties))
            {
                $_aLocallyUpdatedJobs[] = $_oLocalJob->getKey();
            }
        }
        return $_aLocallyUpdatedJobs;
    }


    /**
     * This method should perform any operation that is desired before comparing remote and local entities.
     * Why this is required?
     * For system like marathon, it is essential to set/unset certain values before comparing to make sane
     * comparision.
     *
     * Note: Should be careful with the parameters as they are passed by value.
     *
     * @param JobEntityInterface $oLocalJob
     * @param JobEntityInterface $oRemoteJob
     * @return null
     */
    abstract protected function preCompareModifications(JobEntityInterface &$oLocalJob, JobEntityInterface &$oRemoteJob);

    /**
     * Gets entity for each system with defaults set
     * @return JobEntityInterface
     */
    abstract protected function getEntitySetWithDefaults();

    /**
     * Verify if two entities are equal.
     *
     * @param $sProperty
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return mixed
     */
    abstract protected function isEntityEqual($sProperty, JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB);

    /**
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return bool
     */
    abstract public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB);
}