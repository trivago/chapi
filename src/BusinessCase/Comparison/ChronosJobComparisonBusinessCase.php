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

class ChronosJobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $oJobRepositoryLocalChronos;

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
        JobRepositoryInterface $oJobRepositoryLocalChronos,
        JobRepositoryInterface $oJobRepositoryChronos,
        DiffCompareInterface $oDiffCompare,
        DatePeriodFactoryInterface $oDatePeriodFactory,
        LoggerInterface $oLogger
    )
    {
        $this->oJobRepositoryLocalChronos = $oJobRepositoryLocalChronos;
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
            $this->oJobRepositoryLocalChronos->getJobs(),
            $this->oJobRepositoryChronos->getJobs()
        );
    }

    /**
     * @return string[]
     */
    public function getRemoteMissingJobs()
    {
        $_ret =  $this->getMissingJobsInCollectionA(
            $this->oJobRepositoryChronos->getJobs(),
            $this->oJobRepositoryLocalChronos->getJobs()
        );
        return $_ret;
    }

    /**
     * @return string[]
     */
    public function getLocalJobUpdates()
    {
        $_aJobsLocal = $this->oJobRepositoryLocalChronos->getJobs();
        $_aLocalJobUpdates = [];

        /** @var ChronosJobEntity $_oJobEntityLocal */
        foreach ($_aJobsLocal as $_oJobEntityLocal)
        {
            if (!$_oJobEntityLocal instanceof ChronosJobEntity) {
                throw new \RuntimeException("Entity not chronos entity");
            }

            $_oJobEntityChronos = $this->oJobRepositoryChronos->getJob($_oJobEntityLocal->getKey());

            // if job already exist in chronos (not new or deleted in chronos)
            if ($_oJobEntityChronos && !empty($_oJobEntityChronos->name))
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

        $_oJobEntityLocal = $this->oJobRepositoryLocalChronos->getJob($sJobName);
        if (!$_oJobEntityLocal)
        {
            $_oJobEntityLocal = new ChronosJobEntity();
        }

        $_oJobEntityChronos = $this->oJobRepositoryChronos->getJob($sJobName);
        if (!$_oJobEntityChronos)
        {
            $_oJobEntityChronos = new ChronosJobEntity();
        }

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
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityA
     * @param JobEntityInterface|ChronosJobEntity $oJobEntityB
     * @return bool
     */
    public function hasSameJobType(JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        return (
            ($oJobEntityA->isSchedulingJob() && $oJobEntityB->isSchedulingJob())
            || ($oJobEntityA->isDependencyJob() && $oJobEntityB->isDependencyJob())
        );
    }

    /**
     * @param ChronosJobEntity $oJobEntityA
     * @param ChronosJobEntity $oJobEntityB
     * @return array
     */
    private function compareJobEntities(ChronosJobEntity $oJobEntityA, ChronosJobEntity $oJobEntityB)
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
     * @param ChronosJobEntity $oJobEntityA
     * @param ChronosJobEntity $oJobEntityB
     * @return bool
     */
    private function isJobEntityValueIdentical($sProperty, ChronosJobEntity $oJobEntityA, ChronosJobEntity $oJobEntityB)
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
     * @param ChronosJobEntity $oJobEntityA
     * @param ChronosJobEntity $oJobEntityB
     * @return bool
     */
    private function isScheduleTimeZonePropertyIdentical(ChronosJobEntity $oJobEntityA, ChronosJobEntity $oJobEntityB)
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
     * @param ChronosJobEntity $oJobEntityA
     * @param ChronosJobEntity $oJobEntityB
     * @return bool
     */
    private function isSchedulePropertyIdentical(ChronosJobEntity $oJobEntityA, ChronosJobEntity $oJobEntityB)
    {
        // if values are exact the same
        if ($oJobEntityA->schedule === $oJobEntityB->schedule)
        {
            $this->oLogger->debug(sprintf('%s::EXCACT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
            return true;
        }

        // if one value is empty and not both, compare the time periods
        if (!empty($oJobEntityA->schedule) && !empty($oJobEntityB->schedule))
        {
            $_oIso8601EntityA = $this->oDatePeriodFactory->createIso8601Entity($oJobEntityA->schedule);
            $_oIso8601EntityB = $this->oDatePeriodFactory->createIso8601Entity($oJobEntityB->schedule);

            // if the clean interval is different return directly false (P1D != P1M)
            if ($_oIso8601EntityA->sInterval != $_oIso8601EntityB->sInterval)
            {
                $this->oLogger->debug(sprintf('%s::DIFFERENT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
                return false;
            }

            // else if the interval is <= 1Min return directly true (performance)
            if ($_oIso8601EntityA->sInterval == 'PT1M' || $_oIso8601EntityA->sInterval == 'PT1S')
            {
                $this->oLogger->debug(sprintf('%s::PT1M|PT1S INTERVAL FOR "%s" - Job execution should be equal', 'ScheduleComparison', $oJobEntityA->name));
                return true;
            }

            // start to check by DatePeriods
            $_oLastDateTimeA = null;
            $_oLastDateTimeB = null;

            /** @var \DatePeriod $_oPeriodB */
            $_oPeriodA = $this->oDatePeriodFactory->createDatePeriod($oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone);

            /** @var \DateTime $_oDateTime */
            foreach ($_oPeriodA as $_oDateTime) {
                $_oLastDateTimeA = $_oDateTime;
            }

            /** @var \DatePeriod $_oPeriodB */
            $_oPeriodB = $this->oDatePeriodFactory->createDatePeriod($oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone);

            /** @var \DateTime $_oDateTime */
            foreach ($_oPeriodB as $_oDateTime) {
                $_oLastDateTimeB = $_oDateTime;
            }

            // $_oLastDateTimeA !== false happen if no dates are in the period
            if ($_oLastDateTimeA !== null && $_oLastDateTimeB !== null)
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
     * @param $sJobName
     * @return bool
     */
    public function isJobAvailable($sJobName)
    {
        $_bLocallyAvailable = $this->oJobRepositoryLocalChronos->getJob($sJobName) ? true : false;
        $_bRemotelyAvailable = $this->oJobRepositoryChronos->getJob($sJobName) ? true : false;

        return $_bLocallyAvailable || $_bRemotelyAvailable;
    }
}