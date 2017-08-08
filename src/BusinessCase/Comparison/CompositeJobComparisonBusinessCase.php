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
    private $composites = [];

    /**
     * @param JobComparisonInterface $comparer
     */
    public function addComparisonCases(JobComparisonInterface $comparer)
    {
        $this->composites[] = $comparer;
    }

    /**
     * @return array
     */
    public function getLocalMissingJobs()
    {
        $missingJobs = array();
        foreach ($this->composites as $jobComparers) {
            $missing = $jobComparers->getLocalMissingJobs();
            $missingJobs = array_merge($missingJobs, $missing);
        }
        return $missingJobs;
    }

    /**
     * @return array
     */
    public function getRemoteMissingJobs()
    {
        $chronosMissingJobs = array();
        foreach ($this->composites as $jobComparers) {
            $chronosMissingJobs = array_merge($chronosMissingJobs, $jobComparers->getRemoteMissingJobs());
        }
        return $chronosMissingJobs;
    }

    /**
     * @return array
     */
    public function getLocalJobUpdates()
    {
        $localJobUpdates = array();
        foreach ($this->composites as $jobComparers) {
            $localJobUpdates = array_merge($localJobUpdates, $jobComparers->getLocalJobUpdates());
        }
        return $localJobUpdates;
    }

    /**
     * @param string $jobName
     * @return array
     */
    public function getJobDiff($jobName)
    {
        $jobDiffs = array();
        foreach ($this->composites as $jobComparers) {
            // assuming same name won't be in all subsystems.
            // TODO: add support to handle the duplicate names in different subsystems
            if ($jobComparers->isJobAvailable($jobName)) {
                $jobDiffs = $jobComparers->getJobDiff($jobName);
                break;
            }
        }
        return $jobDiffs;
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $jobEntityA
     * @param JobEntityInterface|ChronosJobEntity $jobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        if ($jobEntityA->getEntityType() != $jobEntityB->getEntityType()) {
            throw new Exception('type compared for different entity types.');
        }
        /** @var JobComparisonInterface $comparer */
        $comparer = null;
        foreach ($this->composites as $child) {
            if ($child->isJobAvailable($jobEntityA->getKey())) {
                $comparer = $child;
                break;
            }
        }

        if ($comparer == null) {
            throw new Exception(sprintf('could not find appropriate comparision businesscase to operate', $jobEntityA->getKey()));
        }

        return $comparer->hasSameJobType($jobEntityA, $jobEntityB);
    }


    /**
     * @param $jobName
     * @return bool
     */
    public function isJobAvailable($jobName)
    {
        foreach ($this->composites as $child) {
            if ($child->isJobAvailable($jobName)) {
                return true;
            }
        }
        return false;
    }
}
