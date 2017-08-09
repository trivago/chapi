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
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var JobComparisonInterface  $jobComparisonBusinessCase */
        $jobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);
        $jobName = $this->input->getArgument('jobName');

        if (!empty($jobName)) {
            $this->printJobDiff($jobName);
        } else {
            $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();
            if (!empty($localJobUpdates)) {
                foreach ($localJobUpdates as $jobName) {
                    $this->printJobDiff($jobName);
                }
            }
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

        foreach ($jobs as $jobName) {
            $this->printSingleJobDiff($jobComparisonBusinessCase, $jobName);
        }
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
            $diffLines = array_reverse(explode(PHP_EOL, $diff));

            foreach ($diffLines as $diffLine) {
                $diffSign = substr($diffLine, 0, 1);

                if ($diffSign == '+') {
                    $this->output->writeln(sprintf("<info>%s\t%s: %s</info>", $diffSign, $property, substr($diffLine, 1)));
                } elseif ($diffSign == '-') {
                    $this->output->writeln(sprintf("<fg=red>%s\t%s: %s</>", $diffSign, $property, substr($diffLine, 1)));
                } else {
                    $this->output->writeln(sprintf("\t%s: %s", $property, $diffLine));
                }
            }
        }

        $this->output->writeln("\n");
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
