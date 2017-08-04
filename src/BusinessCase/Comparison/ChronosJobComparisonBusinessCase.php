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
    private $oDatePeriodFactory;

    /**
     * @var LoggerInterface
     */
    private $oLogger;


    /**
     * @param JobRepositoryInterface $oJobRepositoryLocalChronos
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
    ) {
        $this->oLocalRepository = $oJobRepositoryLocalChronos;
        $this->oRemoteRepository = $oJobRepositoryChronos;
        $this->oDiffCompare = $oDiffCompare;
        $this->oDatePeriodFactory = $oDatePeriodFactory;
        $this->oLogger = $oLogger;
    }


    protected function preCompareModifications(JobEntityInterface &$oLocalJob, JobEntityInterface &$oRemoteJob)
    {
        // no modification needed
        return;
    }


    protected function getEntitySetWithDefaults()
    {
        return new ChronosJobEntity();
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
     * @param string $sProperty
     * @param JobEntityInterface $oJobEntityA
     * @param JobEntityInterface $oJobEntityB
     * @return bool
     */
    protected function isEntityEqual($sProperty, JobEntityInterface $oJobEntityA, JobEntityInterface $oJobEntityB)
    {
        if (!$oJobEntityA instanceof ChronosJobEntity ||
            !$oJobEntityB instanceof ChronosJobEntity
        ) {
            throw new \RuntimeException('Required ChronosJobEntity. Something else encountered');
        }

        $mValueA = $oJobEntityA->{$sProperty};
        $mValueB = $oJobEntityB->{$sProperty};

        switch ($sProperty) {
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
        if ($oJobEntityA->scheduleTimeZone == $oJobEntityB->scheduleTimeZone) {
            return true;
        }

        if (!empty($oJobEntityA->schedule) && !empty($oJobEntityB->schedule)) {
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
        if ($oJobEntityA->schedule === $oJobEntityB->schedule) {
            $this->oLogger->debug(sprintf('%s::EXCACT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
            return true;
        }

        // if one value is empty and not both, compare the time periods
        if (!empty($oJobEntityA->schedule) && !empty($oJobEntityB->schedule)) {
            $_oIso8601EntityA = $this->oDatePeriodFactory->createIso8601Entity($oJobEntityA->schedule);
            $_oIso8601EntityB = $this->oDatePeriodFactory->createIso8601Entity($oJobEntityB->schedule);

            // if the clean interval is different return directly false (P1D != P1M)
            if ($_oIso8601EntityA->sInterval != $_oIso8601EntityB->sInterval) {
                $this->oLogger->debug(sprintf('%s::DIFFERENT INTERVAL FOR "%s"', 'ScheduleComparison', $oJobEntityA->name));
                return false;
            }

            // else if the interval is <= 1Min return directly true (performance)
            if ($_oIso8601EntityA->sInterval == 'PT1M' || $_oIso8601EntityA->sInterval == 'PT1S') {
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
            if ($_oLastDateTimeA !== null && $_oLastDateTimeB !== null) {
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

        if (!empty($sTimeZone)) {
            $_oDateTime = new \DateTime(str_replace('Z', '', $_oIso8601Entity->sStartTime));
            $_oDateTime->setTimezone(new \DateTimeZone($sTimeZone));
        } else {
            $_oDateTime = new \DateTime($_oIso8601Entity->sStartTime);
        }

        return $_oDateTime;
    }
}
