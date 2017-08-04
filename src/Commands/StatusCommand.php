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
    const LABEL_CHRONOS  = 'chronos';
    const LABEL_MARATHON = 'marathon';

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
        $_aChangedJobs = $this->getChangedAppJobs();

        // tracked jobs
        $this->oOutput->writeln("\nChanges to be committed");
        $this->oOutput->writeln("  (use 'chapi reset <job>...' to unstage)");
        $this->oOutput->writeln('');

        $this->printStatusView($_aChangedJobs, true);

        // untracked jobs
        $this->oOutput->writeln("\nChanges not staged for commit");
        $this->oOutput->writeln("  (use 'chapi add <job>...' to update what will be committed)");
        $this->oOutput->writeln("  (use 'chapi checkout <job>...' to discard changes in local repository)");
        $this->oOutput->writeln('');

        $this->printStatusView($_aChangedJobs, false);

        return 0;
    }

    /**
     * @return array<string,array<string,array>>
     */
    private function getChangedAppJobs()
    {
        /** @var JobComparisonInterface $_oJobComparisonBusinessCaseChronos */
        /** @var JobComparisonInterface $_oJobComparisonBusinessCaseMarathon */
        $_oJobComparisonBusinessCaseChronos  = $this->getContainer()->get(JobComparisonInterface::DIC_NAME_CHRONOS);
        $_oJobComparisonBusinessCaseMarathon = $this->getContainer()->get(JobComparisonInterface::DIC_NAME_MARATHON);

        $_aResult = [
            'new' => [
                self::LABEL_CHRONOS => $_oJobComparisonBusinessCaseChronos->getRemoteMissingJobs(),
                self::LABEL_MARATHON => $_oJobComparisonBusinessCaseMarathon->getRemoteMissingJobs(),
            ],
            'missing' => [
                self::LABEL_CHRONOS => $_oJobComparisonBusinessCaseChronos->getLocalMissingJobs(),
                self::LABEL_MARATHON => $_oJobComparisonBusinessCaseMarathon->getLocalMissingJobs(),
            ],
            'updates' => [
                self::LABEL_CHRONOS => $_oJobComparisonBusinessCaseChronos->getLocalJobUpdates(),
                self::LABEL_MARATHON => $_oJobComparisonBusinessCaseMarathon->getLocalJobUpdates(),
            ],
        ];

        return $_aResult;
    }

    /**
     * @param array $aChangedJobs
     * @param bool $bFilterIsInIndex
     */
    private function printStatusView($aChangedJobs, $bFilterIsInIndex)
    {
        $_aFormatMap = [
            'new' => ['title' => 'New jobs in local repository', 'format' => "\t<comment>new %s job:\t%s</comment>"],
            'missing' => ['title' => 'Missing jobs in local repository', 'format' => "\t<fg=red>delete %s job:\t%s</>"],
            'updates' => ['title' => 'Updated jobs in local repository', 'format' => "\t<info>modified %s job:\t%s</info>"]
        ];

        foreach ($aChangedJobs as $_sJobStatus => $_aJobList) {
            $_aFilteredJobList = $this->filterJobListWithIndex($_aJobList, $bFilterIsInIndex);
            if (!empty($_aFilteredJobList)) {
                $this->printJobList($_aFormatMap[$_sJobStatus]['title'], $_aFilteredJobList, $_aFormatMap[$_sJobStatus]['format']);
            }
        }
    }

    /**
     * @param array $aJobLists
     * @param bool $bFilterIsInIndex
     * @return array
     */
    private function filterJobListWithIndex($aJobLists, $bFilterIsInIndex)
    {
        $_aFilteredJobList = [];

        foreach ($aJobLists as $sAppLabel => $aJobList) {
            foreach ($aJobList as $_sJobName) {
                if ($bFilterIsInIndex == $this->oJobIndexService->isJobInIndex($_sJobName)) {
                    $_aFilteredJobList[$sAppLabel][] = $_sJobName;
                }
            }
        }

        return $_aFilteredJobList;
    }

    /**
     * @param string $sTitle
     * @param array $aJobLists
     * @param string $sListFormat
     */
    private function printJobList($sTitle, $aJobLists, $sListFormat)
    {
        $this->oOutput->writeln(sprintf('  %s:', $sTitle));

        foreach ($aJobLists as $sLabel => $aJobList) {
            foreach ($aJobList as $_sJobName) {
                $this->oOutput->writeln(
                    sprintf($sListFormat, $sLabel, $_sJobName)
                );
            }
        }

        $this->oOutput->writeln("\n");
    }
}
