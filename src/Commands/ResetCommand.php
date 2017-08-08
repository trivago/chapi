<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Commands;

use Chapi\Component\Command\JobUtils;
use Chapi\Service\JobIndex\JobIndexServiceInterface;

class ResetCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('reset')
            ->setDescription('Remove jobs from the index')
        ;

        JobUtils::configureJobNamesArgument($this, 'Jobs to remove from the index');
    }

    /**
     * @return int
     */
    protected function process()
    {
        $this->updateJobIndex(
            JobUtils::getJobNames($this->input, $this)
        );

        return 0;
    }

    /**
     * @param string[] $jobNames
     */
    private function updateJobIndex($jobNames)
    {
        /** @var JobIndexServiceInterface  $jobIndexService */
        $jobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);

        if (JobUtils::isWildcard($jobNames)) {
            $jobIndexService->resetJobIndex();
        } else {
            $jobIndexService->removeJobs($jobNames);
        }
    }
}
