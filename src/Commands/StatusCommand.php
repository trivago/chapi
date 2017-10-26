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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends AbstractCommand
{
    const LABEL_CHRONOS  = 'chronos';
    const LABEL_MARATHON = 'marathon';

    /** @var JobIndexServiceInterface  */
    private $jobIndexService;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('status')
            ->setDescription('Show the working tree status')
            ->addOption(
                'strict',
                null,
                InputOption::VALUE_NONE,
                "Return a non-zero exit code when there are changes",
                null
            );
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->jobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
    }

    /**
     * @return int
     */
    protected function process()
    {
        $changedJobs = $this->getChangedAppJobs();

        // tracked jobs
        $this->output->writeln("\nChanges to be committed");
        $this->output->writeln("  (use 'chapi reset <job>...' to unstage)");
        $this->output->writeln('');

        $this->printStatusView($changedJobs, true);

        // untracked jobs
        $this->output->writeln("\nChanges not staged for commit");
        $this->output->writeln("  (use 'chapi add <job>...' to update what will be committed)");
        $this->output->writeln("  (use 'chapi checkout <job>...' to discard changes in local repository)");
        $this->output->writeln('');

        $this->printStatusView($changedJobs, false);

        if ($this->input->getOption('strict') && !empty($changedJobs)) {
            return 1;
        }

        return 0;
    }

    /**
     * @return array<string,array<string,array>>
     */
    private function getChangedAppJobs()
    {
        /** @var JobComparisonInterface $jobComparisonBusinessCaseChronos */
        /** @var JobComparisonInterface $jobComparisonBusinessCaseMarathon */
        $jobComparisonBusinessCaseChronos  = $this->getContainer()->get(JobComparisonInterface::DIC_NAME_CHRONOS);
        $jobComparisonBusinessCaseMarathon = $this->getContainer()->get(JobComparisonInterface::DIC_NAME_MARATHON);

        $result = [
            'new' => [
                self::LABEL_CHRONOS => $jobComparisonBusinessCaseChronos->getRemoteMissingJobs(),
                self::LABEL_MARATHON => $jobComparisonBusinessCaseMarathon->getRemoteMissingJobs(),
            ],
            'missing' => [
                self::LABEL_CHRONOS => $jobComparisonBusinessCaseChronos->getLocalMissingJobs(),
                self::LABEL_MARATHON => $jobComparisonBusinessCaseMarathon->getLocalMissingJobs(),
            ],
            'updates' => [
                self::LABEL_CHRONOS => $jobComparisonBusinessCaseChronos->getLocalJobUpdates(),
                self::LABEL_MARATHON => $jobComparisonBusinessCaseMarathon->getLocalJobUpdates(),
            ],
        ];

        return $result;
    }

    /**
     * @param array $changedJobs
     * @param bool $filterIsInIndex
     */
    private function printStatusView($changedJobs, $filterIsInIndex)
    {
        $formatMap = [
            'new' => ['title' => 'New jobs in local repository', 'format' => "\t<comment>new %s job:\t%s</comment>"],
            'missing' => ['title' => 'Missing jobs in local repository', 'format' => "\t<fg=red>delete %s job:\t%s</>"],
            'updates' => ['title' => 'Updated jobs in local repository', 'format' => "\t<info>modified %s job:\t%s</info>"]
        ];

        foreach ($changedJobs as $jobStatus => $jobList) {
            $filteredJobList = $this->filterJobListWithIndex($jobList, $filterIsInIndex);
            if (!empty($filteredJobList)) {
                $this->printJobList($formatMap[$jobStatus]['title'], $filteredJobList, $formatMap[$jobStatus]['format']);
            }
        }
    }

    /**
     * @param array $jobLists
     * @param bool $filterIsInIndex
     * @return array
     */
    private function filterJobListWithIndex($jobLists, $filterIsInIndex)
    {
        $filteredJobList = [];

        foreach ($jobLists as $appLabel => $jobList) {
            foreach ($jobList as $jobName) {
                if ($filterIsInIndex == $this->jobIndexService->isJobInIndex($jobName)) {
                    $filteredJobList[$appLabel][] = $jobName;
                }
            }
        }

        return $filteredJobList;
    }

    /**
     * @param string $title
     * @param array $jobLists
     * @param string $listFormat
     */
    private function printJobList($title, $jobLists, $listFormat)
    {
        $this->output->writeln(sprintf('  %s:', $title));

        foreach ($jobLists as $label => $jobList) {
            foreach ($jobList as $jobName) {
                $this->output->writeln(
                    sprintf($listFormat, $label, $jobName)
                );
            }
        }

        $this->output->writeln("\n");
    }
}
