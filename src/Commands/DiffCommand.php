<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-30
 *
 */

namespace Chapi\Commands;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Service\JobRepository\JobRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DiffCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('diff')
            ->setDescription('Show changes between jobs and working tree, etc')
            ->addArgument('jobName', InputArgument::OPTIONAL, 'Show changes for specific job')
            ->addOption(
                'strict',
                null,
                InputOption::VALUE_NONE,
                "Return a non-zero exit code when there are changes",
                null
            );
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var JobComparisonInterface  $jobComparisonBusinessCase */
        $jobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);
        $jobName = $this->input->getArgument('jobName');

        $changed = false;

        if (!empty($jobName)) {
            $changed = $this->printJobDiff($jobName);
        } else {
            $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();
            if (!empty($localJobUpdates)) {
                foreach ($localJobUpdates as $jobName) {
                    $changed = $changed || $this->printJobDiff($jobName);
                }
            }
        }

        if ($this->input->getOption('strict') && $changed) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $jobName
     */
    private function printJobDiff($jobName)
    {
        /** @var JobComparisonInterface  $jobComparisonBusinessCase */
        $jobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $jobs = [ $jobName ];

        if (strpos($jobName, '*') !== false) {
            $jobs = $this->getJobsMatchingWildcard($jobName);
        }

        $changed = false;

        foreach ($jobs as $jobName) {
            $changed = $changed || $this->printSingleJobDiff($jobComparisonBusinessCase, $jobName);
        }

        return $changed;
    }

    /**
     * @param JobComparisonInterface $jobComparisonBusinessCase
     * @param string $jobName
     */
    private function printSingleJobDiff(JobComparisonInterface $jobComparisonBusinessCase, $jobName)
    {
        $this->output->writeln(sprintf("\n<comment>diff %s</comment>", $jobName));

        $jobDiff = $jobComparisonBusinessCase->getJobDiff($jobName);

        foreach ($jobDiff as $property => $diff) {
            $diffLines = explode(PHP_EOL, $diff);

            // the first line might be missing some leading whitespace
            if (count($diffLines) > 1) {
                $lastLine = $diffLines[count($diffLines) - 1];

                if (strpos($lastLine, ' ') === 0) {
                    $length = strspn($lastLine, ' ');

                    $diffLines[0] = substr($lastLine, 0, $length) . $diffLines[0];
                }
            }

            foreach ($diffLines as $diffLine) {
                $diffSign = substr($diffLine, 0, 1);

                if ($diffSign == '+') {
                    $this->output->writeln(sprintf("<info>%s\t%s: %s</info>", $diffSign, $property, ' ' . substr($diffLine, 1)));
                } elseif ($diffSign == '-') {
                    $this->output->writeln(sprintf("<fg=red>%s\t%s: %s</>", $diffSign, $property, ' ' . substr($diffLine, 1)));
                } else {
                    $this->output->writeln(sprintf(" \t%s: %s", $property, $diffLine));
                }
            }
        }

        $this->output->writeln("\n");

        return !empty($jobDiff);
    }

    /**
     * @param string $jobName
     * @return string[]
     */
    private function getJobsMatchingWildcard($jobName)
    {
        /** @var JobRepository[] $jobRepositories */
        $jobRepositories = [
            $this->getContainer()->get(JobRepository::DIC_NAME_CHRONOS),
            $this->getContainer()->get(JobRepository::DIC_NAME_FILESYSTEM_CHRONOS),
            $this->getContainer()->get(JobRepository::DIC_NAME_FILESYSTEM_MARATHON),
            $this->getContainer()->get(JobRepository::DIC_NAME_MARATHON)
        ];

        $jobNames = [];

        foreach ($jobRepositories as $jobRepository) {
            foreach ($jobRepository->getJobs() as $job) {
                if (fnmatch($jobName, $job->getKey())) {
                    $jobNames[$job->getKey()] = true;
                }
            }
        }

        ksort($jobNames);

        return array_keys($jobNames);
    }
}
