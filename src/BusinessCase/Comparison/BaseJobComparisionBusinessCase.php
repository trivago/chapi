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

abstract class BaseJobComparisionBusinessCase implements JobComparisonInterface
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
     * @return string[]
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
     * @return array
     */
    public abstract function getLocalJobUpdates();

    /**
     * @param string $sJobName
     * @return array
     */
    public abstract function getJobDiff($sJobName);

    /**
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return bool
     */
    public abstract function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB);
}