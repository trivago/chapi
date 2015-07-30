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
use Chapi\Service\JobRepository\JobRepositoryServiceInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
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
            ->setDescription('Display your job(s) and filter they by status')
            ->addArgument('jobName', InputArgument::OPTIONAL, 'display a specific job', self::DEFAULT_VALUE_JOB_NAME)
            ->addOption('onlyFailed', 'f', InputOption::VALUE_NONE, 'Display only failed jobs')
        ;
    }

    /**
     *
     */
    protected function process()
    {
        /** @var JobRepositoryServiceInterface  $_oJobRepositoryChronos */
        $_oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryServiceInterface::DIC_NAME_CHRONOS);

        $_sJobName = $this->oInput->getArgument('jobName');
        $_bOnlyFailed = (bool) $this->oInput->getOption('onlyFailed');

        if (!empty($_sJobName) && $_sJobName != self::DEFAULT_VALUE_JOB_NAME)
        {
            $_oJobEntity = $_oJobRepositoryChronos->getJob($_sJobName);

            $this->oOutput->writeln(sprintf("\n<comment>list '%s'</comment>\n", $_oJobEntity->name));

            $_oTable = new Table($this->oOutput);
            $_oTable->setHeaders(array('Property', 'Value'));

            foreach ($_oJobEntity as $_sKey => $_mValue)
            {
                if (is_array($_mValue))
                {
                    $_mValue = (!empty($_mValue))
                        ? '[ ' . implode(', ', $_mValue) . ' ]'
                        : '[ ]';
                }
                elseif (is_bool($_mValue))
                {
                    $_mValue = ($_mValue == true)
                        ? 'true'
                        : 'false';
                }

                $_oTable->addRow(array($_sKey, $_mValue));
            }

            $_oTable->render();
        }
        else
        {
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

        }
    }
}