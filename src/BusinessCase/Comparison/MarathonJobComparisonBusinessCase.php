<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 14/12/16
 * Time: 16:12
 */

namespace Chapi\BusinessCase\Comparison;


use Chapi\Component\Comparison\DiffCompareInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class MarathonJobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $oRemoteRepository;
    /**
     * @var JobRepositoryInterface
     */
    private $oLocalRepository;
    /**
     * @var DiffCompareInterface
     */
    private $oDiffCompare;

    /**
     * @param JobRepositoryInterface $oLocalRepository
     * @param JobRepositoryInterface $oRemoteRepository
     * @param DiffCompareInterface $oDiffCompare
     */
    public function __construct(
        JobRepositoryInterface $oLocalRepository,
        JobRepositoryInterface $oRemoteRepository,
        DiffCompareInterface $oDiffCompare
    )
    {

        $this->oRemoteRepository = $oRemoteRepository;
        $this->oLocalRepository = $oLocalRepository;
        $this->oDiffCompare = $oDiffCompare;
    }
    /**
     * @return array
     */
    public function getLocalMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oLocalRepository->getJobs(),
            $this->oRemoteRepository->getJobs()
        );
    }

    /**
     * @return array
     */
    public function getChronosMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oRemoteRepository->getJobs(),
            $this->oLocalRepository->getJobs()
        );
    }

    /**
     * @return array
     */
    public function getLocalJobUpdates()
    {
        $_aLocallyUpdatedJobs = [];
        $_aLocalJobs = $this->oLocalRepository->getJobs();

        /** @var JobEntityInterface $_oLocalJob */
        foreach($_aLocalJobs as $_oLocalJob)
        {
            $_oRemoteJob = $this->oRemoteRepository->getJob($_oLocalJob->getKey());
            if (!$_oRemoteJob)
            {
                $_oRemoteJob = new MarathonAppEntity(null);
            }

            $_aNonIdenticalProps = $this->compareJobEntities($_oLocalJob, $_oRemoteJob);
            if (!empty($_aNonIdenticalProps))
            {
                $_aLocallyUpdatedJobs[] = $_oLocalJob->getKey();
            }
        }

        return $_aLocallyUpdatedJobs;
    }

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobDiff($sJobName)
    {
        $_aDifferences = [];
        $_oLocalJob = $this->oLocalRepository->getJob($sJobName);
        $_oRemoteJob = $this->oRemoteRepository->getJob($sJobName);

        if (!$_oLocalJob && !$_oRemoteJob)
        {
            // return as jobs doesnt exist
            return [];
        }

        if (!$_oLocalJob)
        {
            $_oLocalJob = new MarathonAppEntity(null);
        }

        if (!$_oRemoteJob)
        {
            $_oRemoteJob = new MarathonAppEntity(null);
        }

        $_aNonIdenticalProps = $this->compareJobEntities(
            $_oLocalJob,
            $_oRemoteJob
        );

        foreach ($_aNonIdenticalProps as $_sProperty)
        {
            $_aDifferences[$_sProperty] = $this->oDiffCompare->compare(
                $_oRemoteJob,
                $_oLocalJob
            ) ;
        }

        return $_aDifferences;
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityA
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        // for now we don't have a concrete seperation
        // of types for marathon.
        return true;
    }

    /**
     * @param $sJobName
     * @return bool
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
     * @return string[]
     */
    private function getMissingJobsInCollectionA(JobCollection $oJobCollectionA, JobCollection $oJobCollectionB)
    {
        return array_diff(
            array_keys($oJobCollectionB->getArrayCopy()),
            array_keys($oJobCollectionA->getArrayCopy())
        );
    }

    /**
     * @param ChronosJobEntity $oJobEntityA
     * @param ChronosJobEntity $oJobEntityB
     * @return array
     */
    private function compareJobEntities(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        $_aNonidenticalProperties = [];

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

        $_aDiff = array_merge(
            array_diff_assoc(
                $aJobACopy,
                $aJobBCopy
            ),
            array_diff_assoc(
                $aJobBCopy,
                $aJobACopy
            )
        );

        if (count($_aDiff) > 0)
        {
            $_aDiffKeys = array_keys($_aDiff);
            foreach ($_aDiffKeys as $_sDiffKey)
            {
                $_aNonidenticalProperties[] = $_sDiffKey;

            }
        }

        return $_aNonidenticalProperties;
    }
}