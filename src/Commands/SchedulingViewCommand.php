<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-09
 *
 * @link:    https://github.com/msiebeneicher/chapi/issues/24
 */


namespace Chapi\Commands;

use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\Chronos\JobStatsServiceInterface;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class SchedulingViewCommand extends AbstractCommand
{
    /**
     * @var JobStatsServiceInterface $oJobStatsService
     */
    private $oJobStatsService;

    /**
     * @var JobDependencyServiceInterface $oJobDependencyService
     */
    private $oJobDependencyService;

    /**
     * @var JobRepositoryInterface  $oJobRepositoryChronos
     */
    private $oJobRepositoryChronos;

    /**
     * @var DatePeriodFactoryInterface  $oDatePeriodFactory
     */
    private $oDatePeriodFactory;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('scheduling')
            ->setDescription('Display upcoming jobs in a specified timeframe.')
            ->addOption('starttime', 's', InputOption::VALUE_OPTIONAL, 'Start time to display the jobs', null)
            ->addOption('endtime', 'e', InputOption::VALUE_OPTIONAL, 'End time to display the jobs', null)
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        // init necessary services
        $this->oJobStatsService = $this->getContainer()->get(JobStatsServiceInterface::DIC_NAME);
        $this->oJobDependencyService = $this->getContainer()->get(JobDependencyServiceInterface::DIC_NAME);
        $this->oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);
        $this->oDatePeriodFactory = $this->getContainer()->get(DatePeriodFactoryInterface::DIC_NAME);

        // init timeframe by user input
        $_iCurrentTime = time() + 60; // default 1min in the future

        $_sStartTime = $this->oInput->getOption('starttime');
        $_sEndTime = $this->oInput->getOption('endtime');

        $_iStartTime = ($_sStartTime == null) ? $_iCurrentTime : strtotime($_sStartTime);
        if ($_sStartTime != null && $_iStartTime < $_iCurrentTime)
        {
            $_iStartTime = strtotime($_sStartTime . ' + 24 hours');
        }

        $_iEndTime = ($_sEndTime == null) ? $_iStartTime + 7200 : strtotime($_sEndTime);

        // print table for timeframe
        $this->printTimeLineTable($_iStartTime, $_iEndTime);

        return 0;
    }

    /**
     * @param int $iStartTime
     * @param int $iEndTime
     */
    private function printTimeLineTable($iStartTime, $iEndTime)
    {
        $_oDatePeriod = $this->createDatePeriod(null, $iStartTime, null, $iEndTime, 'PT1M');

        $_oTable = new Table($this->oOutput);
        $_oTable->setHeaders(array(
            'Job',
            'Timeline for ' . date('Y-m-d H:i', $iStartTime) . ' till ' . date('Y-m-d H:i', $iEndTime)
        ));

        $_aJobs = $this->getJobsWhichShouldStartInPeriod($iStartTime, $iEndTime);

        $_iRowCount = 0;

        /** @var JobEntity $_oJobEntity */
        foreach ($_aJobs as $_oJobEntity)
        {
            if (!empty($_oJobEntity->schedule))
            {
                $_bPrintTime = (0 == ($_iRowCount % 5)) ? true : false;
                $_oJobStats = $this->oJobStatsService->getJobStats($_oJobEntity->name);

                $_oJobDatePeriod = $this->createDatePeriodForJob($_oJobEntity, $iEndTime);
                $_fJobRunTime = $_oJobStats->histogram->mean / 1000;

                $_oTable->addRow(
                    array(
                        $_oJobEntity->name,
                        $this->getTimelineStr(
                            $_oDatePeriod,
                            $_oJobDatePeriod,
                            $_fJobRunTime,
                            $_bPrintTime
                        )
                    )
                );

                ++$_iRowCount;

                // print child jobs
                $this->printChildJobs($_oTable, $_oDatePeriod, $_oJobDatePeriod, $_oJobEntity->name, $_fJobRunTime, $_iRowCount, 0);
            }
        }

        $_oTable->render();
    }

    /**
     * @param Table $oTable
     * @param \DatePeriod $oDisplayPeriod
     * @param \DatePeriod $oJobDatePeriod
     * @param string $sParentJobName
     * @param float $fParentJobRunTime
     * @param int $iRowCount
     * @param int $iCurrentChildLevel
     */
    private function printChildJobs(
        Table $oTable,
        \DatePeriod $oDisplayPeriod,
        \DatePeriod $oJobDatePeriod,
        $sParentJobName,
        $fParentJobRunTime,
        &$iRowCount,
        $iCurrentChildLevel = 0
    )
    {
        $_aChildJobs = $this->oJobDependencyService->getChildJobs($sParentJobName, JobDependencyServiceInterface::REPOSITORY_LOCAL);

        foreach ($_aChildJobs as $_sChildJobName)
        {
            $_bPrintTime = (0 == ($iRowCount % 5)) ? true : false;
            $_oChildJobStats = $this->oJobStatsService->getJobStats($_sChildJobName);

            $oTable->addRow(
                array(
                    str_repeat('   ', $iCurrentChildLevel) . '|_ ' . $_sChildJobName,
                    $this->getTimelineStr(
                        $oDisplayPeriod,
                        $oJobDatePeriod,
                        $_oChildJobStats->histogram->mean / 1000,
                        $_bPrintTime,
                        round($fParentJobRunTime)
                    )
                )
            );

            ++$iRowCount;

            // next level
            $this->printChildJobs(
                $oTable,
                $oDisplayPeriod,
                $oJobDatePeriod,
                $_sChildJobName,
                $_oChildJobStats->histogram->mean / 1000 + $fParentJobRunTime,
                $iRowCount,
                ++$iCurrentChildLevel
            );
        }
    }

    /**
     * @param \DatePeriod $oDatePeriod
     * @param \DatePeriod $oJobDatePeriod
     * @param float $fRunSeconds
     * @param boolean $bPrintTime
     * @param int $iJobStartTimeDelay
     * @return string
     */
    private function getTimelineStr(
        \DatePeriod $oDatePeriod,
        \DatePeriod $oJobDatePeriod,
        $fRunSeconds = 0.0,
        $bPrintTime = true,
        $iJobStartTimeDelay = 0
    )
    {
        $_sTimeline = '';
        $_aStartTimes = $this->getJobStartTimesInPeriod($oJobDatePeriod, $iJobStartTimeDelay);

        $_bJobStarted = false;
        $_sSpacer = '-';

        $_iRunMinutes = ($fRunSeconds > 0) ? round($fRunSeconds / 60) : 0;
        $_iPrintedRunMinutes = 0;

        $_bHasToCloseFinalTag = false;

        /** @var \DateTime $_oTime */
        foreach ($oDatePeriod as $_oTime)
        {
            $_bPrintJobEnd = false;
            $_bPrintJobStart = false;

            if (isset($_aStartTimes[$_oTime->format('YmdHi')]))
            {
                $_bJobStarted = true;
                $_bPrintJobStart = true;
                $_sSpacer = '=';
            }

            if ($_bJobStarted)
            {
                ++$_iPrintedRunMinutes;

                if ($_iRunMinutes <= $_iPrintedRunMinutes)
                {
                    $_bJobStarted = false;
                    $_bPrintJobEnd = true;
                    $_sSpacer = '-';
                    $_iPrintedRunMinutes = 0;
                }
            }

            $_iMod = ((int) $_oTime->format('i')) % 15;

            if ($_iMod == 0)
            {
                if ($bPrintTime)
                {
                    $_sTimeline .= $this->parseTimeLineStrMark($_bPrintJobStart, $_bPrintJobEnd, $_bHasToCloseFinalTag, $_oTime->format('H>i'), $_oTime->format('H:i'));

                }
                else
                {
                    $_sTimeline .= $this->parseTimeLineStrMark($_bPrintJobStart, $_bPrintJobEnd, $_bHasToCloseFinalTag, str_repeat('>', 5), str_repeat($_sSpacer, 5));
                }
            }
            else
            {
                $_sTimeline .= $this->parseTimeLineStrMark($_bPrintJobStart, $_bPrintJobEnd, $_bHasToCloseFinalTag, '>', $_sSpacer);
            }
        }

        // add final tag to the end if you runs longer than current timeframe
        if ($_bHasToCloseFinalTag)
        {
            $_sTimeline .= '</comment>';
        }

        return $_sTimeline;
    }

    /**
     * @param bool $bPrintJobStart
     * @param bool $bPrintJobEnd
     * @param bool $bHasToCloseFinalTag
     * @param string $sStartStopMark
     * @param string $sSpacer
     * @return string
     */
    private function parseTimeLineStrMark($bPrintJobStart, $bPrintJobEnd, &$bHasToCloseFinalTag, $sStartStopMark, $sSpacer)
    {
        if ($bPrintJobStart && $bPrintJobEnd)
        {
            $_sTimelineSnippet = sprintf('<comment>%s</comment>', $sStartStopMark);
        }
        elseif ($bPrintJobStart)
        {
            $_sTimelineSnippet = sprintf('<comment>%s', $sStartStopMark);
            $bHasToCloseFinalTag = true;
        }
        elseif ($bPrintJobEnd)
        {
            $_sTimelineSnippet = sprintf('%s</comment>', $sStartStopMark);
            $bHasToCloseFinalTag = false;
        }
        else
        {
            $_sTimelineSnippet = $sSpacer;
        }

        return $_sTimelineSnippet;
    }

    /**
     * @param \DatePeriod $oJobDatePeriod
     * @param int $iJobStartTimeDelay
     * @return array
     */
    private function getJobStartTimesInPeriod(\DatePeriod $oJobDatePeriod, $iJobStartTimeDelay = 0)
    {
        $_aStartTimes = [];

        /** @var \DateTime $_oJobTime */
        foreach ($oJobDatePeriod as $_oJobTime)
        {
            if ($iJobStartTimeDelay > 0)
            {
                $_oJobTime->add(new \DateInterval('PT' . $iJobStartTimeDelay . 'S'));
            }

            $_aStartTimes[$_oJobTime->format('YmdHi')] = $_oJobTime;
        }

        return $_aStartTimes;
    }

    /**
     * @param JobEntity $oJobEntity
     * @param int $iEndTime
     * @return \DatePeriod
     */
    private function createDatePeriodForJob(JobEntity $oJobEntity, $iEndTime)
    {
        $aMatch = $this->oDatePeriodFactory->parseIso8601String($oJobEntity->schedule);

        return $this->createDatePeriod($aMatch[2], 0, null, $iEndTime, $aMatch[3]);
    }

    /**
     * @param string $sDateTimeStart
     * @param int $iTimestampStart
     * @param string $sDateTimeEnd
     * @param int $iTimestampEnd
     * @param string $sDateInterval
     * @return \DatePeriod
     */
    private function createDatePeriod($sDateTimeStart = '', $iTimestampStart = 0, $sDateTimeEnd = '', $iTimestampEnd = 0, $sDateInterval = 'PT1M')
    {
        $_oDateStart = new \DateTime($sDateTimeStart);
        if ($iTimestampStart > 0)
        {
            $_oDateStart->setTimestamp($iTimestampStart);
        }

        $_oDateEnd = new \DateTime($sDateTimeEnd);
        if ($iTimestampEnd > 0)
        {
            $_oDateEnd->setTimestamp($iTimestampEnd);
        }

        $_oDateInterval = new \DateInterval($sDateInterval);

        return new \DatePeriod($_oDateStart, $_oDateInterval, $_oDateEnd);
    }

    /**
     * @param int $iStartTime
     * @param int $iEndTime
     * @return JobEntity[]
     */
    private function getJobsWhichShouldStartInPeriod($iStartTime, $iEndTime)
    {
        $_oJobCollection = $this->oJobRepositoryChronos->getJobs();

        $_aJobs = [];

        /** @var JobEntity $_oJobEntity */
        foreach ($_oJobCollection as $_oJobEntity)
        {
            if (
                $_oJobEntity->isSchedulingJob()
                && false === $_oJobEntity->disabled
                && false === strpos($_oJobEntity->schedule, 'R0')
            )
            {
                $_oJobDatePeriod = $this->createDatePeriodForJob($_oJobEntity, $iEndTime);
                if ($this->isPeriodInTimeFrame($_oJobDatePeriod, $iStartTime, $iEndTime))
                {
                    $_aJobs[] = $_oJobEntity;
                }
            }
        }

        return $_aJobs;
    }

    /**
     * @param \DatePeriod $_oJobDatePeriod
     * @param int $iStartTime
     * @param int $iEndTime
     * @return bool
     */
    private function isPeriodInTimeFrame(\DatePeriod $_oJobDatePeriod, $iStartTime, $iEndTime)
    {
        $_iLastTime = 0;

        /** @var \DateTime $_oJobTime */
        foreach ($_oJobDatePeriod as $_oJobTime)
        {
            // jobs under 1 hours should always be displayed (break after the second loop)
            if ($_oJobTime->getTimestamp() - $_iLastTime <= 3600)
            {
                return true;
            }

            // is one starting point in timeframe?
            if ($_oJobTime->getTimestamp() >= $iStartTime && $_oJobTime->getTimestamp() <= $iEndTime)
            {
                return true;
            }

            $_iLastTime = $_oJobTime->getTimestamp();
        }

        return false;
    }
}