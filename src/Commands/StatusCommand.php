<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Chapi\Service\JobRepository\JobRepositoryServiceInterface;

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
        /** @var JobRepositoryServiceInterface  $_oJobRepositoryLocal */
        $_oJobRepositoryLocal = $this->getContainer()->get(JobRepositoryServiceInterface::DIC_NAME_FILESYSTEM);
        /** @var JobRepositoryServiceInterface  $_oJobRepositoryChronos */
        $_oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryServiceInterface::DIC_NAME_CHRONOS);


        $_aJobsA = $_oJobRepositoryLocal->getJobs()->getArrayCopy();
        $_aJobsB = $_oJobRepositoryChronos->getJobs()->getArrayCopy();

        // new jobs
        $_aNewJobs = array_diff(
            array_keys($_aJobsA),
            array_keys($_aJobsB)
        );

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
        $_aMissingJobs = array_diff(
            array_keys($_aJobsB),
            array_keys($_aJobsA)
        );

        if (!empty($_aMissingJobs))
        {
            $this->oOutput->writeln(sprintf('%s:', 'Missing jobs in local repository'));

            foreach ($_aMissingJobs as $_sValue)
            {
                $this->oOutput->writeln(sprintf("\t<comment>delete job:\t%s</comment>", $_sValue));
            }

            $this->oOutput->writeln("\n");
        }
    }
}