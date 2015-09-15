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
        $_aJobNames = JobUtils::getJobNames($this->oInput, $this);
        $_aJobsToAdd = (JobUtils::isWildcard($_aJobNames))
            ? $this->getChangedJobs()
            : $_aJobNames
        ;

        $this->addJobs($_aJobsToAdd);

        return 0;
    }

    /**
     * @return array
     */
    private function getChangedJobs()
    {
        // job data
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $_aNewJobs = $_oJobComparisonBusinessCase->getChronosMissingJobs();
        $_aMissingJobs = $_oJobComparisonBusinessCase->getLocalMissingJobs();
        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        return array_merge($_aNewJobs, $_aMissingJobs, $_aLocalJobUpdates);
    }

    /**
     * @param string[] $aJobsToAdd
     */
    private function addJobs($aJobsToAdd)
    {
        /** @var JobIndexServiceInterface  $_oJobIndexService */
        $_oJobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
        $_oJobIndexService->addJobs($aJobsToAdd);
    }
}