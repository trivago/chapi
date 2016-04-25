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
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Psr\Log\LoggerInterface;

class JobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryChronos;

    /**
     * @var DiffCompareInterface
     */
    private $oDiffCompare;

    /**
     * @var DatePeriodFactoryInterface
     */
    private $oDatePeriodFactory;

    /**
     * @var LoggerInterface
     */
    private $oLogger;


    /**
     * @param JobRepositoryInterface $oJobRepositoryLocal
     * @param JobRepositoryInterface $oJobRepositoryChronos
     * @param DiffCompareInterface $oDiffCompare
     * @param DatePeriodFactoryInterface $oDatePeriodFactory
     * @param LoggerInterface $oLogger
     */
    public function __construct(
        JobRepositoryInterface $oJobRepositoryLocal,
        JobRepositoryInterface $oJobRepositoryChronos,
        DiffCompareInterface $oDiffCompare,
        DatePeriodFactoryInterface $oDatePeriodFactory,
        LoggerInterface $oLogger
    )
    {
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
        $this->oDiffCompare = $oDiffCompare;
        $this->oDatePeriodFactory = $oDatePeriodFactory;
        $this->oLogger = $oLogger;
    }

    /**
     * @return string[]
     */
    public function getLocalMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oJobRepositoryLocal->getJobs(),
            $this->oJobRepositoryChronos->getJobs()
        );
    }

    /**
     * @return string[]
     */
    public function getChronosMissingJobs()
    {
        return $this->getMissingJobsInCollectionA(
            $this->oJobRepositoryChronos->getJobs(),
            $this->oJobRepositoryLocal->getJobs()
        );
    }

    /**
     * @return string[]
     */
    public function getLocalJobUpdates()
    {
        $_aJobsLocal = $this->oJobRepositoryLocal->getJobs();
        $_aLocalJobUpdates = [];

        /** @var JobEntity $_oJobEntity */
        foreach ($_aJobsLocal as $_oJobEntityLocal)
        {
            $_oJobEntityChronos = $this->oJobRepositoryChronos->getJob($_oJobEntityLocal->name);

            // if job already exist in chronos (not new or deleted in chronos)
            if (!empty($_oJobEntityChronos->name))
            {
                $_aNonidenticalProperties = $this->compareJobEntities($_oJobEntityLocal, $_oJobEntityChronos);
                if (!empty($_aNonidenticalProperties))
                {
                    $_aLocalJobUpdates[] = $_oJobEntityLocal->name;
                }
            }
        }

        return $_aLocalJobUpdates;
    }

    /**
     * @param string $sJobName
     * @return string[]
     */
    public function getJobDiff($sJobName)
    {
        $_aReturn = [];

        $_oJobEntityLocal = $this->oJobRepositoryLocal->getJob($sJobName);
        $_oJobEntityChronos = $this->oJobRepositoryChronos->getJob($sJobName);

        $_aNonidenticalProperties = $this->compareJobEntities(
            $_oJobEntityLocal,
            $_oJobEntityChronos
        );

        foreach ($_aNonidenticalProperties as $_sProperty)
        {
            $_aReturn[$_sProperty] = $this->oDiffCompare->compare(
                $_oJobEntityChronos->{$_sProperty},
                $_oJobEntityLocal->{$_sProperty}
            );
        }

        return $_aReturn;
    }

    /**
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        return (
            ($oJobEntityA->isSchedulingJob() && $oJobEntityB->isSchedulingJob())
            || ($oJobEntityA->isDependencyJob() && $oJobEntityB->isDependencyJob())
        );
    }

    /**
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return array
     */
    private function compareJobEntities(JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        $_aNonidenticalProperties = [];

        $_aDiff = array_merge(
            array_diff_assoc(
                $oJobEntityA->getSimpleArrayCopy(),
                $oJobEntityB->getSimpleArrayCopy()
            ),
            array_diff_assoc(
                $oJobEntityB->getSimpleArrayCopy(),
                $oJobEntityA->getSimpleArrayCopy()
            )
        );

        if (count($_aDiff) > 0)
        {
            $_aDiffKeys = array_keys($_aDiff);
            foreach ($_aDiffKeys as $_sDiffKey)
            {
                if (!$this->isJobEntityValueIdentical($_sDiffKey, $oJobEntityA, $oJobEntityB))
                {
                    $_aNonidenticalProperties[] = $_sDiffKey;
                }
            }
        }

        return $_aNonidenticalProperties;
    }

    /**
     * @param string $sProperty
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return bool
     */
    private function isJobEntityValueIdentical($sProperty, JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        $mValueA = $oJobEntityA->{$sProperty};
        $mValueB = $oJobEntityB->{$sProperty};

        switch ($sProperty)
        {
            case 'schedule':
                return $this->isSchedulePropertyIdentical($oJobEntityA, $oJobEntityB);

            case 'scheduleTimeZone':
                return $this->isScheduleTimeZonePropertyIdentical($oJobEntityA, $oJobEntityB);

            case 'parents':
                return (
                    is_array($mValueA)
                    && is_array($mValueB)
                    && count(array_diff($mValueA, $mValueB)) == 0
                    && count(array_diff($mValueB, $mValueA)) == 0
                );

            case 'successCount':
            case 'lastSuccess':
            case 'errorCount':
            case 'errorsSinceLastSuccess':
            case 'lastError':
                return true;

            default:
                return ($mValueA == $mValueB);
        }
    }

    /**
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return bool
     */
    private function isScheduleTimeZonePropertyIdentical(JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        if ($oJobEntityA->scheduleTimeZone == $oJobEntityB->scheduleTimeZone)
        {
            return true;
        }

        if (!empty($oJobEntityA->schedule) && !empty($oJobEntityB->schedule))
        {
            $_oDateA = $this->createDateTimeObj($oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone);
            $_oDateB = $this->createDateTimeObj($oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone);

            return ($_oDateA->getOffset() == $_oDateB->getOffset());
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntityA
     * @param JobEntity $oJobEntityB
     * @return bool
     */
    private function isSchedulePropertyIdentical(JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        // if values are exact the same
//var_dump(sprintf('A :: %s :: %s', $oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone));
//var_dump(sprintf('B :: %s :: %s', $oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone));
//var_dump('---------------');
        if ($oJobEntityA->schedule === $oJobEntityB->schedule)
        {
            $this->oLogger->debug(sprintf('%s::EXCACT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
            return true;
        }

        // if one value is empty and not both, compare the time periods
        if (!empty($oJobEntityA->schedule) && !empty($oJobEntityB->schedule))
        {
            // if the clean interval is different return directly false (P1D != P1M)
            if (!$this->isEqualInterval($oJobEntityA->schedule, $oJobEntityB->schedule))
            {
                $this->oLogger->debug(sprintf('%s::DIFFERENT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
                return false;
            }

            // start to check by DatePeriods
            $_aDatesA = [];
            $_aDatesB = [];

            /** @var \DatePeriod $_oPeriodB */
            $_oPeriodA = $this->oDatePeriodFactory->createDatePeriod($oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone);

            /** @var \DateTime $_oDateTime */
            foreach ($_oPeriodA as $_oDateTime) {
                $_aDatesA[] = $_oDateTime;
            }

            /** @var \DatePeriod $_oPeriodB */
            $_oPeriodB = $this->oDatePeriodFactory->createDatePeriod($oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone);

            /** @var \DateTime $_oDateTime */
            foreach ($_oPeriodB as $_oDateTime) {
                $_aDatesB[] = $_oDateTime;
            }

            /** @var \DateTime $_oLastDateTimeA */
            $_oLastDateTimeA = end($_aDatesA);
            /** @var \DateTime $_oLastDateTimeB */
            $_oLastDateTimeB = end($_aDatesB);

            // $_oLastDateTimeA !== false happen if no dates are in the period
            if ($_oLastDateTimeA !== false && $_oLastDateTimeB !== false)
            {
                $_oDiffInterval = $_oLastDateTimeA->diff($_oLastDateTimeB);
                $_iDiffInterval = (int) $_oDiffInterval->format('%Y%M%D%H%I');

                $this->oLogger->debug(sprintf('%s::INTERVAL DIFF OF "%d" FOR "%s"', 'ScheduleComparison', $_iDiffInterval, $oJobEntityA->name));

                return ($_iDiffInterval == 0);
            }
        }

        $this->oLogger->warning(sprintf('%s::CAN\'T COMPARE INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
        return false;
    }

    /**
     * @param string $sIso8601String
     * @param string $sTimeZone
     * @return \DateTime
     */
    private function createDateTimeObj($sIso8601String, $sTimeZone = '')
    {
        $_oIso8601Entity = $this->oDatePeriodFactory->createIso8601Entity($sIso8601String);

        if (!empty($sTimeZone))
        {
            $_oDateTime = new \DateTime(str_replace('Z', '', $_oIso8601Entity->sStartTime));
            $_oDateTime->setTimezone(new \DateTimeZone($sTimeZone));
        }
        else
        {
            $_oDateTime = new \DateTime($_oIso8601Entity->sStartTime);
        }

        return $_oDateTime;
    }

    /**
     * @param string $sIso8601StringA
     * @param string $sIso8601StringB
     * @return bool
     */
    private function isEqualInterval($sIso8601StringA, $sIso8601StringB)
    {
        $_oIso8601EntityA = $this->oDatePeriodFactory->createIso8601Entity($sIso8601StringA);
        $_oIso8601EntityB = $this->oDatePeriodFactory->createIso8601Entity($sIso8601StringB);

        return ($_oIso8601EntityA->sInterval == $_oIso8601EntityB->sInterval);
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
}