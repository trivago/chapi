<?php
/**
 * @package: chapi
 *
 * @author: bthapaliya
 * @since: 2017-01-18
 *
 */

namespace Chapi\BusinessCase\Comparison;

use Chapi\Component\Comparison\DiffCompareInterface;
use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

class CompositeJobComparisonBusinessCase implements JobComparisonInterface
{
    /** @var JobComparisonInterface[] */
    private $aComposites = [];

    public function addComparisonCases(JobComparisonInterface $comparer)
    {
        $this->aComposites[] = $comparer;
    }

    /**
     * @return array
     */
    public function getLocalMissingJobs()
    {
        $_aMissingJobs = array();
        foreach($this->aComposites as $jobComparers)
        {
            $missing = $jobComparers->getLocalMissingJobs();
            $_aMissingJobs = array_merge($_aMissingJobs,  $missing);
        }
        return $_aMissingJobs;
    }

    /**
     * @return array
     */
    public function getRemoteMissingJobs()
    {
        $_aChronosMissingJobs = array();
        foreach($this->aComposites as $jobComparers)
        {
            $_aChronosMissingJobs = array_merge($_aChronosMissingJobs, $jobComparers->getRemoteMissingJobs());
        }
        return $_aChronosMissingJobs;
    }

    /**
     * @return array
     */
    public function getLocalJobUpdates()
    {
        $_aLocalJobUpdates = array();
        foreach($this->aComposites as $jobComparers)
        {
            $_aLocalJobUpdates = array_merge($_aLocalJobUpdates, $jobComparers->getLocalJobUpdates());
        }
        return $_aLocalJobUpdates;
    }

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobDiff($sJobName)
    {
        $_aJobDiffs = array();
        foreach($this->aComposites as $jobComparers)
        {
            // NOTE: only gets the first one.
            // does it make sense?
            if ($jobComparers->isJobAvailable($sJobName)) {
                $_aJobDiffs = $jobComparers->getJobDiff($sJobName);
                break;
            }
        }
        return $_aJobDiffs;
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityA
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        if ($oJobEntityA->getEntityType() != $oJobEntityB->getEntityType()) {
            throw new Exception("type compared for different entity types.");
        }
        /** @var JobComparisonInterface $comparer */
        $comparer = null;
        foreach($this->aComposites as $child)
        {
            if ($child->isJobAvailable($oJobEntityA->getKey()))
            {
                $comparer = $child;
                break;
            }
        }

        if ($comparer == null)
        {
            throw new Exception(sprintf("could not find appropriate comparision businesscase to operate", $oJobEntityA->getKey()));
        }

        return $comparer->hasSameJobType($oJobEntityA, $oJobEntityB);
    }


    /**
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName)
    {
        foreach($this->aComposites as $child) {
            if ($child->isJobAvailable($sJobName))
            {
                return true;
            }
        }
        return false;
    }
}