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
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobRepository\JobRepositoryServiceInterface;

class JobComparisonBusinessCase implements JobComparisonInterface
{
    /**
     * @var JobRepositoryServiceInterface
     */
    private $oJobRepositoryLocal;

    /**
     * @var JobRepositoryServiceInterface
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
     * @param JobRepositoryServiceInterface $oJobRepositoryLocal
     * @param JobRepositoryServiceInterface $oJobRepositoryChronos
     * @param DiffCompareInterface $oDiffCompare
     * @param DatePeriodFactoryInterface $oDatePeriodFactory
     */
    public function __construct(
        JobRepositoryServiceInterface $oJobRepositoryLocal,
        JobRepositoryServiceInterface $oJobRepositoryChronos,
        DiffCompareInterface $oDiffCompare,
        DatePeriodFactoryInterface $oDatePeriodFactory
    )
    {
        $this->oJobRepositoryLocal = $oJobRepositoryLocal;
        $this->oJobRepositoryChronos = $oJobRepositoryChronos;
        $this->oDiffCompare = $oDiffCompare;
        $this->oDatePeriodFactory = $oDatePeriodFactory;
    }

    /**
     * @return array
     */
    public function getLocalMissingJobs()
    {
        $_aJobsLocal = $this->oJobRepositoryLocal->getJobs()->getArrayCopy();
        $_aJobsChronos = $this->oJobRepositoryChronos->getJobs()->getArrayCopy();

        // missing jobs
        $_aMissingJobs = array_diff(
            array_keys($_aJobsChronos),
            array_keys($_aJobsLocal)
        );

        return $_aMissingJobs;
    }

    /**
     * @return array
     */
    public function getChronosMissingJobs()
    {
        $_aJobsLocal = $this->oJobRepositoryLocal->getJobs()->getArrayCopy();
        $_aJobsChronos = $this->oJobRepositoryChronos->getJobs()->getArrayCopy();

        $_aJobs = array_diff(
            array_keys($_aJobsLocal),
            array_keys($_aJobsChronos)
        );

        return $_aJobs;
    }

    /**
     * @return array
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
     * @param $sJobName
     * @return array
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
     * @return array
     */
    private function compareJobEntities(JobEntity $oJobEntityA, JobEntity $oJobEntityB)
    {
        $_aNonidenticalProperties = [];

        $_aDiff = array_merge(
            array_diff(
                $oJobEntityA->getSimpleArrayCopy(),
                $oJobEntityB->getSimpleArrayCopy()
            ),
            array_diff(
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
     * @param $sProperty
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

                $_aDatesA = [];
                if (!empty($mValueA))
                {
                    $_oPeriodA = $this->oDatePeriodFactory->createDatePeriod($oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone);

                    /** @var \DateTime $_oDateTime */
                    foreach($_oPeriodA as $_oDateTime){
                        $_aDatesA[] = $_oDateTime->format("Y-m-dH:i");
                    }
                }

                $_aDatesB = [];

                if (!empty($mValueB))
                {
                    $_oPeriodB = $this->oDatePeriodFactory->createDatePeriod($oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone);

                    /** @var \DateTime $_oDateTime */
                    foreach($_oPeriodB as $_oDateTime){
                        $_aDatesB[] = $_oDateTime->format("Y-m-dH:i");
                    }
                }

                return (end($_aDatesA) == end($_aDatesB));
                break;

            case 'scheduleTimeZone':
                if ($mValueA == $mValueB)
                {
                    return true;
                }

                if (!empty($oJobEntityA->schedule) && !empty($oJobEntityA->schedule))
                {
                    $_oDateA = $this->createDateTimeObj($oJobEntityA->schedule, $oJobEntityA->scheduleTimeZone);
                    $_oDateB = $this->createDateTimeObj($oJobEntityB->schedule, $oJobEntityB->scheduleTimeZone);

                    return ($_oDateA->getOffset() == $_oDateB->getOffset());
                }
                break;

            case 'parents':
                return (
                    is_array($mValueA)
                    && is_array($mValueB)
                    && count(array_diff($mValueA, $mValueB)) == 0
                    && count(array_diff($mValueB, $mValueA)) == 0
                );
                break;

            case 'successCount':
            case 'lastSuccess':
            case 'errorCount':
            case 'errorsSinceLastSuccess':
            case 'lastError':
                return true;
                break;

            default:
                return ($mValueA == $mValueB);
                break;
        }
    }

    /**
     * @param string $sIso8601String
     * @param string $sTimeZone
     * @return \DateTime
     */
    private function createDateTimeObj($sIso8601String, $sTimeZone = '')
    {
        $aMatch = $this->oDatePeriodFactory->parseIso8601String($sIso8601String);

        if (!empty($sTimeZone))
        {
            $_oDateTime = new \DateTime(str_replace('Z', '', $aMatch[2]));
            $_oDateTime->setTimezone(new \DateTimeZone($sTimeZone));
        }
        else
        {
            $_oDateTime = new \DateTime($aMatch[2]);
        }

        return $_oDateTime;
    }
}