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

class StatusCommand extends AbstractCommand
{
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
     *
     */
    protected function process()
    {
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        // new jobs
        $_aNewJobs = $_oJobComparisonBusinessCase->getChronosMissingJobs();

        if (!empty($_aNewJobs))
        {
            $this->oOutput->writeln(sprintf('%s:', 'Changes to be committed'));

            foreach ($_aNewJobs as $_sValue)
            {
                $this->oOutput->writeln(sprintf("\t<comment>new job:\t%s</comment>", $_sValue));
            }

            $this->oOutput->writeln("\n");
        }

        // missing jobs
        $_aMissingJobs = $_oJobComparisonBusinessCase->getLocalMissingJobs();

        if (!empty($_aMissingJobs))
        {
            $this->oOutput->writeln(sprintf('%s:', 'Missing jobs in local repository'));

            foreach ($_aMissingJobs as $_sValue)
            {
                $this->oOutput->writeln(sprintf("\t<comment>delete job:\t%s</comment>", $_sValue));
            }

            $this->oOutput->writeln("\n");
        }

        // updated jobs
        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();
        if (!empty($_aLocalJobUpdates))
        {
            $this->oOutput->writeln(sprintf('%s:', 'Changes for commit'));

            foreach ($_aLocalJobUpdates as $_sJobName => $_aDiffProperties)
            {
                $this->oOutput->writeln(sprintf("\t<info>modified job:\t%s</info>", $_sJobName));
            }

            $this->oOutput->writeln("\n");
        }
    }
}