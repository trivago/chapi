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
            JobUtils::getJobNames($this->oInput, $this)
        );

        return 0;
    }

    /**
     * @param string[] $aJobNames
     */
    private function updateJobIndex($aJobNames)
    {
        /** @var JobIndexServiceInterface  $_oJobIndexService */
        $_oJobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);

        if (JobUtils::isWildcard($aJobNames))
        {
            $_oJobIndexService->resetJobIndex();
        }
        else
        {
            $_oJobIndexService->removeJobs($aJobNames);
        }
    }
}