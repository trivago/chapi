<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Symfony\Component\Console\Input\InputOption;

class ListCommand extends AbstractCommand
{
    const DEFAULT_VALUE_JOB_NAME = 'all';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('list')
            ->setDescription('Display your jobs and filter they by status')
            ->addOption('onlyFailed', 'f', InputOption::VALUE_NONE, 'Display only failed jobs')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var JobRepositoryInterface  $_oJobRepositoryChronos */
        $_oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);

        $_bOnlyFailed = (bool) $this->oInput->getOption('onlyFailed');

        /** @var JobEntity $_oJobEntity */
        foreach ($_oJobRepositoryChronos->getJobs() as $_oJobEntity)
        {
            if (
                ($_bOnlyFailed && $_oJobEntity->errorsSinceLastSuccess > 0)
                || $_bOnlyFailed == false
            )
            {
                if ($_oJobEntity->errorsSinceLastSuccess > 0)
                {

                    $this->oOutput->writeln(sprintf("\t<fg=red>%s</>", $_oJobEntity->name));
                }
                elseif ($_oJobEntity->errorCount > 0)
                {
                    $this->oOutput->writeln(sprintf("\t<comment>%s</comment>", $_oJobEntity->name));
                }
                else
                {
                    $this->oOutput->writeln(sprintf("\t<info>%s</info>", $_oJobEntity->name));
                }
            }
        }

        return 0;
    }
}