<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Commands;

use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Component\Command\JobUtils;
use Chapi\Service\JobIndex\JobIndexServiceInterface;

class AddCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('Add job contents to the index')
        ;

        JobUtils::configureJobNamesArgument($this, 'Jobs to add to the index');
    }

    /**
     * @return int
     */
    protected function process()
    {
        $jobNames = JobUtils::getJobNames($this->input, $this);
        $jobsToAdd = (JobUtils::isWildcard($jobNames))
            ? $this->getChangedJobs()
            : $jobNames
        ;

        $this->addJobs($jobsToAdd);

        return 0;
    }

    /**
     * @return array
     */
    private function getChangedJobs()
    {
        // job data
        /** @var JobComparisonInterface  $jobComparisonBusinessCase */
        $jobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $newJobs = $jobComparisonBusinessCase->getRemoteMissingJobs();
        $missingJobs = $jobComparisonBusinessCase->getLocalMissingJobs();
        $localJobUpdates = $jobComparisonBusinessCase->getLocalJobUpdates();

        return array_merge($newJobs, $missingJobs, $localJobUpdates);
    }

    /**
     * @param string[] $jobsToAdd
     */
    private function addJobs($jobsToAdd)
    {
        /** @var JobIndexServiceInterface  $jobIndexService */
        $jobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
        $jobIndexService->addJobs($jobsToAdd);
    }
}
