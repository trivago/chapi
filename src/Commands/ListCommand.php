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
use Symfony\Component\Console\Helper\Table;
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
            ->addOption('onlyDisabled', 'd', InputOption::VALUE_NONE, 'Display only disabled jobs')
        ;
    }

    /**
     * @return int
     * @throws \LogicException
     */
    protected function process()
    {
        /** @var JobRepositoryInterface  $_oJobRepositoryChronos */
        $_oJobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);

        $_bOnlyFailed = (bool) $this->oInput->getOption('onlyFailed');
        $_bOnlyDisabled = (bool) $this->oInput->getOption('onlyDisabled');

        $_oTable = new Table($this->oOutput);
        $_oTable->setHeaders(array(
            'Job',
            'Info'
        ));

        /** @var JobEntity $_oJobEntity */
        foreach ($_oJobRepositoryChronos->getJobs() as $_oJobEntity)
        {
            if ($this->hasJobToPrint($_oJobEntity, $_bOnlyFailed, $_bOnlyDisabled))
            {
                $this->printJobTableRow($_oTable, $_oJobEntity);
            }
        }

        $_oTable->render();

        return 0;
    }

    /**
     * @param JobEntity $oJobEntity
     * @param bool $bOnlyFailed
     * @param bool $bOnlyDisabled
     * @return bool
     */
    private function hasJobToPrint(JobEntity $oJobEntity, $bOnlyFailed, $bOnlyDisabled)
    {
        $_bPrintAllJobs = (false === $bOnlyFailed && false === $bOnlyDisabled);
        if ($_bPrintAllJobs)
        {
            return true;
        }

        $_bHasToPrint = false;

        if (true === $bOnlyFailed && $oJobEntity->errorsSinceLastSuccess > 0)
        {
            $_bHasToPrint = true;
        }

        if (true === $bOnlyDisabled && true === $oJobEntity->disabled)
        {
            $_bHasToPrint = true;
        }

        return $_bHasToPrint;
    }

    /**
     * @param Table $oTable
     * @param JobEntity $oJobEntity
     */
    private function printJobTableRow(Table $oTable, JobEntity $oJobEntity)
    {
        $oTable->addRow([
            sprintf(
                $this->getOutputFormat($oJobEntity),
                $oJobEntity->name
            ),

            sprintf(
                $this->getOutputFormat($oJobEntity),
                $this->getOutputLabel($oJobEntity)
            ),
        ]);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return string
     */
    private function getOutputLabel(JobEntity $oJobEntity)
    {
        $_aJobInfoText = [];

        if ($oJobEntity->disabled)
        {
            $_aJobInfoText[] = 'disabled';
        }

        if ($oJobEntity->errorCount > 0)
        {
            $_fErrorRate = ($oJobEntity->successCount > 0)
                ? 100 / $oJobEntity->successCount * $oJobEntity->errorCount
                : 100;

            $_aJobInfoText[] = 'errors rate: ' . round($_fErrorRate, 2) . '%';
        }

        if ($oJobEntity->errorsSinceLastSuccess > 0)
        {
            $_aJobInfoText[] = 'errors since last success:' . $oJobEntity->errorsSinceLastSuccess;
        }

        return (!empty($_aJobInfoText))
            ? implode(' | ', $_aJobInfoText)
            : 'ok';
    }

    /**
     * @param JobEntity $oJobEntity
     * @return string
     */
    private function getOutputFormat(JobEntity $oJobEntity)
    {
        if ($oJobEntity->errorsSinceLastSuccess > 0)
        {
            return '<fg=red>%s</>';
        }

        if ($oJobEntity->errorCount > 0 || true == $oJobEntity->disabled)
        {
            return '<comment>%s</comment>';
        }

        // else
        return '<info>%s</info>';
    }
}