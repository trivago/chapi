<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractCommand
{
    /** @var JobIndexServiceInterface  */
    private $oJobIndexService;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('status')
            ->setDescription('Show the working tree status')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::initialize($oInput, $oOutput);

        $this->oJobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
    }

    /**
     * @return int
     */
    protected function process()
    {
        // job data
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $_aNewJobs = $_oJobComparisonBusinessCase->getChronosMissingJobs();
        $_aMissingJobs = $_oJobComparisonBusinessCase->getLocalMissingJobs();
        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        // tracked jobs
        $this->oOutput->writeln("\nChanges to be committed");
        $this->oOutput->writeln("  (use 'chapi reset <job>...' to unstage)");
        $this->oOutput->writeln("");

        $this->printStatusView(true, $_aNewJobs, $_aMissingJobs, $_aLocalJobUpdates);

        // untracked jobs
        $this->oOutput->writeln("\nChanges not staged for commit");
        $this->oOutput->writeln("  (use 'chapi add <job>...' to update what will be committed)");
        $this->oOutput->writeln("  (use 'chapi checkout <job>...' to discard changes in local repository)");
        $this->oOutput->writeln("");

        $this->printStatusView(false, $_aNewJobs, $_aMissingJobs, $_aLocalJobUpdates);

        return 0;
    }

    /**
     * @param $bJobIsInIndex
     * @param $aNewJobs
     * @param $aMissingJobs
     * @param $aLocalJobUpdates
     */
    private function printStatusView($bJobIsInIndex, $aNewJobs, $aMissingJobs, $aLocalJobUpdates)
    {
        // new jobs
        if (!empty($aNewJobs))
        {
            $this->printJobListComparedWithIndex($bJobIsInIndex, 'New jobs in local repository', $aNewJobs, "\t<comment>new job:\t%s</comment>");
        }

        // missing jobs
        if (!empty($aMissingJobs))
        {
            $this->printJobListComparedWithIndex($bJobIsInIndex, 'Missing jobs in local repository', $aMissingJobs, "\t<fg=red>delete job:\t%s</>");
        }

        // updated jobs
        if (!empty($aLocalJobUpdates))
        {
            $this->printJobListComparedWithIndex($bJobIsInIndex, 'Updated jobs in local repository', $aLocalJobUpdates, "\t<info>modified job:\t%s</info>");
        }
    }

    /**
     * @param $bJobIsInIndex
     * @param $sTitle
     * @param $aJobList
     * @param $sListFormat
     */
    private function printJobListComparedWithIndex($bJobIsInIndex, $sTitle, $aJobList, $sListFormat)
    {
        $_aFilteredJobList = [];

        foreach ($aJobList as $_sJobName)
        {
            if (true == $bJobIsInIndex)
            {
                if ($this->oJobIndexService->isJobInIndex($_sJobName))
                {
                    $_aFilteredJobList[] = $_sJobName;
                }
            }
            else
            {
                if (!$this->oJobIndexService->isJobInIndex($_sJobName))
                {
                    $_aFilteredJobList[] = $_sJobName;
                }
            }
        }

        if (!empty($_aFilteredJobList))
        {
            $this->printJobList($sTitle, $_aFilteredJobList, $sListFormat);
        }

    }

    /**
     * @param $sTitle
     * @param $aJobList
     * @param $sListFormat
     * @return $this
     */
    private function printJobList($sTitle, $aJobList, $sListFormat)
    {
        $this->oOutput->writeln(sprintf('  %s:', $sTitle));

        foreach ($aJobList as $_sJobName)
        {
            $this->oOutput->writeln(sprintf($sListFormat, $_sJobName));
        }

        $this->oOutput->writeln("\n");

        return $this;
    }
}