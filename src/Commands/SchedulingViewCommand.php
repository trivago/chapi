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
use Chapi\Entity\Chronos\JobStatsEntity;
use Chapi\Service\Chronos\JobStatsServiceInterface;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class SchedulingViewCommand extends AbstractCommand
{
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
        $_iCurrentTime = time();

        $_sStartTime = $this->oInput->getOption('starttime');
        $_sEndTime = $this->oInput->getOption('endtime');

        $_iStartTime = ($_sStartTime == null) ? $_iCurrentTime : strtotime($_sStartTime);
        if ($_sStartTime != null && $_iStartTime < $_iCurrentTime)
        {
            $_iStartTime = strtotime($_sStartTime . ' + 24 hours');
        }

        $_iEndTime = ($_sEndTime == null) ? $_iStartTime + 7200 : strtotime($_sEndTime);


        $_oDateStart = new \DateTime();
        $_oDateStart->setTimestamp($_iStartTime);

        $_oDateInterval = new \DateInterval('PT1M');
        $_oDataEnd = new \DateTime();
        $_oDataEnd->setTimestamp($_iEndTime);

        $_oDatePeriod = new \DatePeriod($_oDateStart, $_oDateInterval, $_oDataEnd);


        $_oTable = new Table($this->oOutput);
        $_oTable->setHeaders(array(
            'Job',
            'Timeline for ' . date('Y-m-d H:i', $_iStartTime) . ' till ' . date('Y-m-d H:i', $_iEndTime)
        ));

        $_aJobs = $this->getJobsWhichShouldStartInPeriod($_iStartTime, $_iEndTime);

        $_i = 0;
        /** @var JobStatsServiceInterface $_oJobStatsService */
        $_oJobStatsService = $this->getContainer()->get(JobStatsServiceInterface::DIC_NAME);



        /** @var JobEntity $_oJobEntity */
        foreach ($_aJobs as $_oJobEntity)
        {
            if (!empty($_oJobEntity->schedule))
            {
                $_bPrintTime = (0 == ($_i % 5)) ? true : false;
                $_oJobStats = $_oJobStatsService->getJobStats($_oJobEntity->name);

                $_oJobDatePeriod = $this->createDatePeriodForJob($_oJobEntity, $_iEndTime);
                $_fJobRunTime = $_oJobStats->histogram->mean / 1000;

                $_oTable->addRow(
                    array(
                        $_oJobEntity->name,
                        $this->getTimeline(
                            $_oDatePeriod,
                            $_oJobDatePeriod,
                            $_fJobRunTime,
                            $_bPrintTime
                        )
                    )
                );

                ++$_i;

                // print child jobs
                $this->printChildJobs($_oTable, $_oDatePeriod, $_oJobDatePeriod, $_oJobEntity->name, $_fJobRunTime, $_i, 0);
            }
        }

        $_oTable->render();

        return 0;
    }

    private function printChildJobs(Table $oTable, \DatePeriod $oDisplayPeriod, \DatePeriod $oJobDatePeriod, $sParentJobName, $fParentJobRunTime, &$iRowCount, $iCurrentChildLevel = 0)
    {
        /** @var JobDependencyServiceInterface $_oJobDependencyService */
        $_oJobDependencyService = $this->getContainer()->get(JobDependencyServiceInterface::DIC_NAME);

        /** @var JobStatsServiceInterface $_oJobStatsService */
        $_oJobStatsService = $this->getContainer()->get(JobStatsServiceInterface::DIC_NAME);

        $_aChildJobs = $_oJobDependencyService->getChildJobs($sParentJobName, JobDependencyServiceInterface::REPOSITORY_LOCAL);
        foreach ($_aChildJobs as $_sChildJobName)
        {
            $_bPrintTime = (0 == ($iRowCount % 5)) ? true : false;
            $_oChildJobStats = $_oJobStatsService->getJobStats($_sChildJobName);

            $oTable->addRow(
                array(
                    str_repeat('   ', $iCurrentChildLevel) . '|_ ' . $_sChildJobName,
                    $this->getTimeline(
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
            $this->printChildJobs($oTable, $oDisplayPeriod, $oJobDatePeriod, $_sChildJobName, $fParentJobRunTime + $_oChildJobStats->histogram->mean / 1000, $iRowCount, ++$iCurrentChildLevel);
        }
    }

    private function getTimeline($_oDatePeriod, $oJobDatePeriod, $fRunSeconds = 0.0, $bPrintTime = true, $iJobStartTimeDelay = 0)
    {
        $_sTimeline = '';
        $_aStartTimes = $this->getJobStartTimesInPeriod($oJobDatePeriod, $iJobStartTimeDelay);

        $_bJobStarted = false;
        $_sSpacer = '-';

        $_iRunMinutes = ($fRunSeconds > 0) ? round($fRunSeconds / 60) : 0;
        $_iPrintedRunMinutes = 0;


        /** @var \DateTime $_oTime */
        $_bHasToCloseFinalTag = false;

        foreach ($_oDatePeriod as $_oTime)
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
                    //$_sTimeline .= ($_bPrintJobStart || $_bPrintJobEnd) ? $_oTime->format('H>i') : $_oTime->format('H:i');
                    if ($_bPrintJobStart && $_bPrintJobEnd)
                    {
                        $_sTimeline .= '<comment>' . $_oTime->format('H>i') . '</comment>';
                    }
                    elseif ($_bPrintJobStart)
                    {
                        $_sTimeline .= '<comment>' . $_oTime->format('H>i');
                        $_bHasToCloseFinalTag = true;
                    }
                    elseif ($_bPrintJobEnd)
                    {
                        $_sTimeline .= $_oTime->format('H>i') . '</comment>';
                        $_bHasToCloseFinalTag = false;
                    }
                    else
                    {
                        $_sTimeline .= $_oTime->format('H:i');
                    }
                }
                else
                {
                    //$_sTimeline .= ($_bPrintJobStart || $_bPrintJobEnd) ? '>>>>>' : str_repeat($_sSpacer, 5);
                    if ($_bPrintJobStart && $_bPrintJobEnd)
                    {
                        $_sTimeline .= '<comment>>>>>></comment>';
                    }
                    elseif ($_bPrintJobStart)
                    {
                        $_sTimeline .= '<comment>>>>>>';
                        $_bHasToCloseFinalTag = true;
                    }
                    elseif ($_bPrintJobEnd)
                    {
                        $_sTimeline .= '>>>>></comment>';
                        $_bHasToCloseFinalTag = false;
                    }
                    else
                    {
                        $_sTimeline .= str_repeat($_sSpacer, 5);
                    }
                }

            }
            else
            {
                /** @var \DateTime $_oJobTime */

                //$_sTimeline .= ($_bPrintJobStart || $_bPrintJobEnd) ? '>' : $_sSpacer;
                if ($_bPrintJobStart && $_bPrintJobEnd)
                {
                    $_sTimeline .= '<comment>></comment>';
                }
                elseif ($_bPrintJobStart)
                {
                    $_sTimeline .= '<comment>>';
                    $_bHasToCloseFinalTag = true;
                }
                elseif ($_bPrintJobEnd)
                {
                    $_sTimeline .= '></comment>';
                    $_bHasToCloseFinalTag = false;
                }
                else
                {
                    $_sTimeline .= $_sSpacer;
                }
            }
        }

        // add final tag to the end if you runs longer than current timeframe
        if ($_bHasToCloseFinalTag)
        {
            $_sTimeline .= '</comment>';
        }

        return $_sTimeline;
    }

    private function getJobStartTimesInPeriod(\DatePeriod $oJobDatePeriod, $iJobStartTimeDelay = 0)
    {
        $_aStartTimes = [];

        /** @var \DateTime $_oJobTime */
        foreach ($oJobDatePeriod as $_oJobTime)
        {
            if ($iJobStartTimeDelay > 0)
            {

                $_oJobTime->add(new \DateInterval('PT'.$iJobStartTimeDelay.'S'));
            }

            $_aStartTimes[$_oJobTime->format('YmdHi')] = $_oJobTime;
        }

        return $_aStartTimes;
    }



    private function createDatePeriodForJob(JobEntity $oJobEntity, $iEndTime)
    {
        /** @var DatePeriodFactoryInterface  $_oDatePeriodFactory */
        $_oDatePeriodFactory = $this->getContainer()->get(DatePeriodFactoryInterface::DIC_NAME);
        $aMatch = $_oDatePeriodFactory->parseIso8601String($oJobEntity->schedule);

        $_oDateStart = new \DateTime($aMatch[2]);
        $_oDateInterval = new \DateInterval($aMatch[3]);
        $_oDateEnd = new \DateTime();
        $_oDateEnd->setTimestamp($iEndTime);

        return new \DatePeriod($_oDateStart, $_oDateInterval, $_oDateEnd);
    }

    private function getJobsWhichShouldStartInPeriod($iStartTime, $iEndTime)
    {
        /** @var JobRepositoryInterface  $_oJobRepositoryChronos */
        $_oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);
        $_oJobCollection = $_oJobRepositoryChronos->getJobs();

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
                /** @var \DateTime $_oJobTime */

                $_iLastTime = 0;
                foreach ($_oJobDatePeriod as $_oJobTime)
                {
                    // jobs under 1 hours should always be displayed (break after the second loop)
                    if ($_oJobTime->getTimestamp() - $_iLastTime <= 3600)
                    {
                        $_aJobs[] = $_oJobEntity;
                        break;
                    }

                    // is one starting point in timeframe?
                    if ($_oJobTime->getTimestamp() >= $iStartTime && $_oJobTime->getTimestamp() <= $iEndTime)
                    {
                        $_aJobs[] = $_oJobEntity;
                        break;
                    }

                    $_iLastTime = $_oJobTime->getTimestamp();
                }
            }
        }

        return $_aJobs;
    }
}