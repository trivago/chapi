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
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\Chronos\JobStatsServiceInterface;
use Chapi\Service\JobDependencies\JobDependencyServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

class SchedulingViewCommand extends AbstractCommand
{
    /**
     * @var JobStatsServiceInterface $jobStatsService
     */
    private $jobStatsService;

    /**
     * @var JobDependencyServiceInterface $jobDependencyService
     */
    private $jobDependencyService;

    /**
     * @var JobRepositoryInterface  $jobRepositoryChronos
     */
    private $jobRepositoryChronos;

    /**
     * @var DatePeriodFactoryInterface  $datePeriodFactory
     */
    private $datePeriodFactory;

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
        $this->jobStatsService = $this->getContainer()->get(JobStatsServiceInterface::DIC_NAME);
        $this->jobDependencyService = $this->getContainer()->get(JobDependencyServiceInterface::DIC_NAME);
        $this->jobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);
        $this->datePeriodFactory = $this->getContainer()->get(DatePeriodFactoryInterface::DIC_NAME);

        // init timeframe by user input
        $currentTime = time() + 60; // default 1min in the future

        $startTimeString = $this->input->getOption('starttime');
        $endTimeString = $this->input->getOption('endtime');

        $startTime = ($startTimeString == null) ? $currentTime : strtotime($startTimeString);
        if ($startTimeString != null && $startTime < $currentTime) {
            $startTime = strtotime($startTimeString . ' + 24 hours');
        }

        $endTime = ($endTimeString == null) ? $startTime + 7200 : strtotime($endTimeString);

        // print table for timeframe
        $this->printTimeLineTable($startTime, $endTime);

        return 0;
    }

    /**
     * @param int $startTime
     * @param int $endTime
     */
    private function printTimeLineTable($startTime, $endTime)
    {
        $datePeriod = $this->createDatePeriod(null, $startTime, null, $endTime, 'PT1M');

        $table = new Table($this->output);
        $table->setHeaders(array(
            'Job',
            'Timeline for ' . date('Y-m-d H:i', $startTime) . ' till ' . date('Y-m-d H:i', $endTime)
        ));

        $jobs = $this->getJobsWhichShouldStartInPeriod($startTime, $endTime);

        $rowCount = 0;

        /** @var ChronosJobEntity $jobEntity */
        foreach ($jobs as $jobEntity) {
            if (!empty($jobEntity->schedule)) {
                $printTime = (0 == ($rowCount % 5)) ? true : false;
                $jobStats = $this->jobStatsService->getJobStats($jobEntity->name);

                $jobDatePeriod = $this->createDatePeriodForJob($jobEntity, $endTime);
                $jobRunTime = $jobStats->histogram->mean / 1000;

                $table->addRow(
                    array(
                        $jobEntity->name,
                        $this->getTimelineStr(
                            $datePeriod,
                            $jobDatePeriod,
                            $jobRunTime,
                            $printTime
                        )
                    )
                );

                ++$rowCount;

                // print child jobs
                $this->printChildJobs($table, $datePeriod, $jobDatePeriod, $jobEntity->name, $jobRunTime, $rowCount, 0);
            }
        }

        $table->render();
    }

    /**
     * @param Table $table
     * @param \DatePeriod $displayPeriod
     * @param \DatePeriod $jobDatePeriod
     * @param string $parentJobName
     * @param float $parentJobRunTime
     * @param int $rowCount
     * @param int $currentChildLevel
     */
    private function printChildJobs(
        Table $table,
        \DatePeriod $displayPeriod,
        \DatePeriod $jobDatePeriod,
        $parentJobName,
        $parentJobRunTime,
        &$rowCount,
        $currentChildLevel = 0
    ) {
        $childJobs = $this->jobDependencyService->getChildJobs($parentJobName, JobDependencyServiceInterface::REPOSITORY_LOCAL);

        foreach ($childJobs as $childJobName) {
            $printTime = (0 == ($rowCount % 5)) ? true : false;
            $childJobStats = $this->jobStatsService->getJobStats($childJobName);

            $table->addRow(
                array(
                    str_repeat('   ', $currentChildLevel) . '|_ ' . $childJobName,
                    $this->getTimelineStr(
                        $displayPeriod,
                        $jobDatePeriod,
                        $childJobStats->histogram->mean / 1000,
                        $printTime,
                        round($parentJobRunTime)
                    )
                )
            );

            ++$rowCount;

            // next level
            $this->printChildJobs(
                $table,
                $displayPeriod,
                $jobDatePeriod,
                $childJobName,
                $childJobStats->histogram->mean / 1000 + $parentJobRunTime,
                $rowCount,
                ++$currentChildLevel
            );
        }
    }

    /**
     * @param \DatePeriod $datePeriod
     * @param \DatePeriod $jobDatePeriod
     * @param float $runSeconds
     * @param boolean $printTime
     * @param int $jobStartTimeDelay
     * @return string
     */
    private function getTimelineStr(
        \DatePeriod $datePeriod,
        \DatePeriod $jobDatePeriod,
        $runSeconds = 0.0,
        $printTime = true,
        $jobStartTimeDelay = 0
    ) {
        $timeline = '';
        $startTimes = $this->getJobStartTimesInPeriod($jobDatePeriod, $jobStartTimeDelay);

        $jobStarted = false;
        $spacer = '-';

        $runMinutes = ($runSeconds > 0) ? round($runSeconds / 60) : 0;
        $printedRunMinutes = 0;

        $hasToCloseFinalTag = false;

        /** @var \DateTime $time */
        foreach ($datePeriod as $time) {
            $printJobEnd = false;
            $printJobStart = false;

            if (isset($startTimes[$time->format('YmdHi')])) {
                $jobStarted = true;
                $printJobStart = true;
                $spacer = '=';
            }

            if ($jobStarted) {
                ++$printedRunMinutes;

                if ($runMinutes <= $printedRunMinutes) {
                    $jobStarted = false;
                    $printJobEnd = true;
                    $spacer = '-';
                    $printedRunMinutes = 0;
                }
            }

            $mod = ((int) $time->format('i')) % 15;

            if ($mod == 0) {
                if ($printTime) {
                    $timeline .= $this->parseTimeLineStrMark($printJobStart, $printJobEnd, $hasToCloseFinalTag, $time->format('H>i'), $time->format('H:i'));
                } else {
                    $timeline .= $this->parseTimeLineStrMark($printJobStart, $printJobEnd, $hasToCloseFinalTag, str_repeat('>', 5), str_repeat($spacer, 5));
                }
            } else {
                $timeline .= $this->parseTimeLineStrMark($printJobStart, $printJobEnd, $hasToCloseFinalTag, '>', $spacer);
            }
        }

        // add final tag to the end if you runs longer than current timeframe
        if ($hasToCloseFinalTag) {
            $timeline .= '</comment>';
        }

        return $timeline;
    }

    /**
     * @param bool $printJobStart
     * @param bool $printJobEnd
     * @param bool $bHasToCloseFinalTag
     * @param string $startStopMark
     * @param string $spacer
     * @return string
     */
    private function parseTimeLineStrMark($printJobStart, $printJobEnd, &$bHasToCloseFinalTag, $startStopMark, $spacer)
    {
        if ($printJobStart && $printJobEnd) {
            $timelineSnippet = sprintf('<comment>%s</comment>', $startStopMark);
        } elseif ($printJobStart) {
            $timelineSnippet = sprintf('<comment>%s', $startStopMark);
            $bHasToCloseFinalTag = true;
        } elseif ($printJobEnd) {
            $timelineSnippet = sprintf('%s</comment>', $startStopMark);
            $bHasToCloseFinalTag = false;
        } else {
            $timelineSnippet = $spacer;
        }

        return $timelineSnippet;
    }

    /**
     * @param \DatePeriod $jobDatePeriod
     * @param int $jobStartTimeDelay
     * @return array
     */
    private function getJobStartTimesInPeriod(\DatePeriod $jobDatePeriod, $jobStartTimeDelay = 0)
    {
        $startTimes = [];

        /** @var \DateTime $jobTime */
        foreach ($jobDatePeriod as $jobTime) {
            if ($jobStartTimeDelay > 0) {
                $jobTime->add(new \DateInterval('PT' . $jobStartTimeDelay . 'S'));
            }

            $startTimes[$jobTime->format('YmdHi')] = $jobTime;
        }

        return $startTimes;
    }

    /**
     * @param ChronosJobEntity $jobEntity
     * @param int $endTime
     * @return \DatePeriod
     */
    private function createDatePeriodForJob(ChronosJobEntity $jobEntity, $endTime)
    {
        $iso8601Entity = $this->datePeriodFactory->createIso8601Entity($jobEntity->schedule);
        return $this->createDatePeriod($iso8601Entity->startTime, 0, null, $endTime, $iso8601Entity->interval);
    }

    /**
     * @param string $dateTimeStart
     * @param int $timestampStart
     * @param string $dateTimeEnd
     * @param int $timestampEnd
     * @param string $dateInterval
     * @return \DatePeriod
     */
    private function createDatePeriod($dateTimeStart = '', $timestampStart = 0, $dateTimeEnd = '', $timestampEnd = 0, $dateInterval = 'PT1M')
    {
        $dateStart = new \DateTime($dateTimeStart);
        if ($timestampStart > 0) {
            $dateStart->setTimestamp($timestampStart);
        }

        $dateEnd = new \DateTime($dateTimeEnd);
        if ($timestampEnd > 0) {
            $dateEnd->setTimestamp($timestampEnd);
        }

        $_oDateInterval = new \DateInterval($dateInterval);

        return new \DatePeriod($dateStart, $_oDateInterval, $dateEnd);
    }

    /**
     * @param int $startTime
     * @param int $endTime
     * @return ChronosJobEntity[]
     */
    private function getJobsWhichShouldStartInPeriod($startTime, $endTime)
    {
        $jobCollection = $this->jobRepositoryChronos->getJobs();

        $jobs = [];

        /** @var ChronosJobEntity $jobEntity */
        foreach ($jobCollection as $jobEntity) {
            if ($jobEntity->isSchedulingJob()
                && false === $jobEntity->disabled
                && false === strpos($jobEntity->schedule, 'R0')
            ) {
                $jobDatePeriod = $this->createDatePeriodForJob($jobEntity, $endTime);
                if ($this->isPeriodInTimeFrame($jobDatePeriod, $startTime, $endTime)) {
                    $jobs[] = $jobEntity;
                }
            }
        }

        return $jobs;
    }

    /**
     * @param \DatePeriod $jobDatePeriod
     * @param int $startTime
     * @param int $endTime
     * @return bool
     */
    private function isPeriodInTimeFrame(\DatePeriod $jobDatePeriod, $startTime, $endTime)
    {
        $lastTime = 0;

        /** @var \DateTime $jobTime */
        foreach ($jobDatePeriod as $jobTime) {
            // jobs under 1 hours should always be displayed (break after the second loop)
            if ($jobTime->getTimestamp() - $lastTime <= 3600) {
                return true;
            }

            // is one starting point in timeframe?
            if ($jobTime->getTimestamp() >= $startTime && $jobTime->getTimestamp() <= $endTime) {
                return true;
            }

            $lastTime = $jobTime->getTimestamp();
        }

        return false;
    }
}
