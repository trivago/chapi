<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
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

class ChronosJobComparisonBusinessCase extends AbstractJobComparisionBusinessCase
{
    /**
     * @var DatePeriodFactoryInterface
     */
    private $datePeriodFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @param JobRepositoryInterface $jobRepositoryLocalChronos
     * @param JobRepositoryInterface $jobRepositoryChronos
     * @param DiffCompareInterface $diffCompare
     * @param DatePeriodFactoryInterface $datePeriodFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        JobRepositoryInterface $jobRepositoryLocalChronos,
        JobRepositoryInterface $jobRepositoryChronos,
        DiffCompareInterface $diffCompare,
        DatePeriodFactoryInterface $datePeriodFactory,
        LoggerInterface $logger
    ) {
        $this->localRepository = $jobRepositoryLocalChronos;
        $this->remoteRepository = $jobRepositoryChronos;
        $this->diffCompare = $diffCompare;
        $this->datePeriodFactory = $datePeriodFactory;
        $this->logger = $logger;
    }


    protected function preCompareModifications(JobEntityInterface &$localJob, JobEntityInterface &$remoteJob)
    {
        // no modification needed
        return;
    }


    protected function getEntitySetWithDefaults()
    {
        return new ChronosJobEntity();
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $jobEntityA
     * @param JobEntityInterface|ChronosJobEntity $jobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        return (
            ($jobEntityA->isSchedulingJob() && $jobEntityB->isSchedulingJob())
            || ($jobEntityA->isDependencyJob() && $jobEntityB->isDependencyJob())
        );
    }

    /**
     * @param string $property
     * @param JobEntityInterface $jobEntityA
     * @param JobEntityInterface $jobEntityB
     * @return bool
     */
    protected function isEntityEqual($property, JobEntityInterface $jobEntityA, JobEntityInterface $jobEntityB)
    {
        if (!$jobEntityA instanceof ChronosJobEntity ||
            !$jobEntityB instanceof ChronosJobEntity
        ) {
            throw new \RuntimeException('Required ChronosJobEntity. Something else encountered');
        }

        $valueA = $jobEntityA->{$property};
        $valueB = $jobEntityB->{$property};

        switch ($property) {
            case 'schedule':
                return $this->isSchedulePropertyIdentical($jobEntityA, $jobEntityB);

            case 'scheduleTimeZone':
                return $this->isScheduleTimeZonePropertyIdentical($jobEntityA, $jobEntityB);

            case 'parents':
                return (
                    is_array($valueA)
                    && is_array($valueB)
                    && count(array_diff($valueA, $valueB)) == 0
                    && count(array_diff($valueB, $valueA)) == 0
                );

            case 'successCount':
            case 'lastSuccess':
            case 'errorCount':
            case 'errorsSinceLastSuccess':
            case 'lastError':
                return true;

            default:
                return ($valueA == $valueB);
        }
    }

    /**
     * @param ChronosJobEntity $jobEntityA
     * @param ChronosJobEntity $jobEntityB
     * @return bool
     */
    private function isScheduleTimeZonePropertyIdentical(ChronosJobEntity $jobEntityA, ChronosJobEntity $jobEntityB)
    {
        if ($jobEntityA->scheduleTimeZone == $jobEntityB->scheduleTimeZone) {
            return true;
        }

        if (!empty($jobEntityA->schedule) && !empty($jobEntityB->schedule)) {
            $dateA = $this->createDateTimeObj($jobEntityA->schedule, $jobEntityA->scheduleTimeZone);
            $dateB = $this->createDateTimeObj($jobEntityB->schedule, $jobEntityB->scheduleTimeZone);

            return ($dateA->getOffset() == $dateB->getOffset());
        }

        return false;
    }

    /**
     * @param ChronosJobEntity $jobEntityA
     * @param ChronosJobEntity $jobEntityB
     * @return bool
     */
    private function isSchedulePropertyIdentical(ChronosJobEntity $jobEntityA, ChronosJobEntity $jobEntityB)
    {
        // if values are exact the same
        if ($jobEntityA->schedule === $jobEntityB->schedule) {
            $this->logger->debug(sprintf('%s::EXCACT INTERVAL FOR "%s"', 'ScheduleComparison', $jobEntityA->name));
            return true;
        }

        // if one value is empty and not both, compare the time periods
        if (!empty($jobEntityA->schedule) && !empty($jobEntityB->schedule)) {
            $iso8601EntityA = $this->datePeriodFactory->createIso8601Entity($jobEntityA->schedule);
            $iso8601EntityB = $this->datePeriodFactory->createIso8601Entity($jobEntityB->schedule);

            // if the clean interval is different return directly false (P1D != P1M)
            if ($iso8601EntityA->interval != $iso8601EntityB->interval) {
                $this->logger->debug(sprintf('%s::DIFFERENT INTERVAL FOR "%s"', 'ScheduleComparison', $jobEntityA->name));
                return false;
            }

            // else if the interval is <= 1Min return directly true (performance)
            if ($iso8601EntityA->interval == 'PT1M' || $iso8601EntityA->interval == 'PT1S') {
                $this->logger->debug(sprintf('%s::PT1M|PT1S INTERVAL FOR "%s" - Job execution should be equal', 'ScheduleComparison', $jobEntityA->name));
                return true;
            }

            // start to check by DatePeriods
            $lastDateTimeA = null;
            $lastDateTimeB = null;

            /** @var \DatePeriod $periodB */
            $periodA = $this->datePeriodFactory->createDatePeriod($jobEntityA->schedule, $jobEntityA->scheduleTimeZone);

            /** @var \DateTime $dateTime */
            foreach ($periodA as $dateTime) {
                $lastDateTimeA = $dateTime;
            }

            /** @var \DatePeriod $periodB */
            $periodB = $this->datePeriodFactory->createDatePeriod($jobEntityB->schedule, $jobEntityB->scheduleTimeZone);

            /** @var \DateTime $dateTime */
            foreach ($periodB as $dateTime) {
                $lastDateTimeB = $dateTime;
            }

            // $lastDateTimeA !== false happen if no dates are in the period
            if ($lastDateTimeA !== null && $lastDateTimeB !== null) {
                $diffInterval = $lastDateTimeA->diff($lastDateTimeB);
                $formattedDiffInterval = (int) $diffInterval->format('%Y%M%D%H%I');

                $this->logger->debug(sprintf('%s::INTERVAL DIFF OF "%d" FOR "%s"', 'ScheduleComparison', $formattedDiffInterval, $jobEntityA->name));
                return ($formattedDiffInterval == 0);
            }
        }

        $this->logger->warning(sprintf('%s::CAN\'T COMPARE INTERVAL FOR "%s"', 'ScheduleComparison', $jobEntityA->name));
        return false;
    }

    /**
     * @param string $iso8601String
     * @param string $timeZone
     * @return \DateTime
     */
    private function createDateTimeObj($iso8601String, $timeZone = '')
    {
        $iso8601Entity = $this->datePeriodFactory->createIso8601Entity($iso8601String);

        if (!empty($timeZone)) {
            $dateTime = new \DateTime(str_replace('Z', '', $iso8601Entity->startTime));
            $dateTime->setTimezone(new \DateTimeZone($timeZone));
        } else {
            $dateTime = new \DateTime($iso8601Entity->startTime);
        }

        return $dateTime;
    }
}
