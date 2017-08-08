<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Commands;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\Marathon\MarathonAppEntity;
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
            ->setDescription('Display your jobs and filter them by status')
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
        /** @var JobRepositoryInterface  $jobRepositoryChronos */
        $jobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);
        /** @var  JobRepositoryInterface $jobRepositoryMarathon */
        $jobRepositoryMarathon = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_MARATHON);

        $onlyPrintFailed = (bool) $this->input->getOption('onlyFailed');
        $onlyPrintDisabled = (bool) $this->input->getOption('onlyDisabled');

        $table = new Table($this->output);
        $table->setHeaders(array(
            'Job',
            'Info',
            'Type'
        ));

        $allEntities = array_merge(
            $jobRepositoryChronos->getJobs()->getArrayCopy(),
            $jobRepositoryMarathon->getJobs()->getArrayCopy()
        );

        /** @var ChronosJobEntity $jobEntity */
        foreach ($allEntities as $jobEntity) {
            if ($this->hasJobToPrint($jobEntity, $onlyPrintFailed, $onlyPrintDisabled)) {
                $this->printJobTableRow($table, $jobEntity);
            }
        }

        $table->render();

        return 0;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @param bool $onlyPrintFailed
     * @param bool $onlyPrintDisabled
     * @return bool
     */
    private function hasJobToPrint(JobEntityInterface $jobEntity, $onlyPrintFailed, $onlyPrintDisabled)
    {
        if ($jobEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE) {
            return true;
        }

        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Entity not of type ChronosJobEntity');
        }

        $printAllJobs = (false === $onlyPrintFailed && false === $onlyPrintDisabled);
        if ($printAllJobs) {
            return true;
        }

        $hasToPrint = false;

        if (true === $onlyPrintFailed && $jobEntity->errorsSinceLastSuccess > 0) {
            $hasToPrint = true;
        }

        if (true === $onlyPrintDisabled && true === $jobEntity->disabled) {
            $hasToPrint = true;
        }

        return $hasToPrint;
    }

    /**
     * @param Table $table
     * @param JobEntityInterface $jobEntity
     */
    private function printJobTableRow(Table $table, JobEntityInterface $jobEntity)
    {
        $table->addRow([
            sprintf(
                $this->getOutputFormat($jobEntity),
                $jobEntity->getKey()
            ),

            sprintf(
                $this->getOutputFormat($jobEntity),
                $this->getOutputLabel($jobEntity)
            ),
            sprintf(
                $this->getOutputFormat($jobEntity),
                $jobEntity->getEntityType()
            )
        ]);
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return string
     */
    private function getOutputLabel(JobEntityInterface $jobEntity)
    {

        if ($jobEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE) {
            return 'ok';
        }

        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Entity not of type ChronosJobEntity');
        }

        $jobInfoText = [];

        if ($jobEntity->disabled) {
            $jobInfoText[] = 'disabled';
        }

        if ($jobEntity->errorCount > 0) {
            $errorRate = ($jobEntity->successCount > 0)
                ? 100 / $jobEntity->successCount * $jobEntity->errorCount
                : 100;

            $jobInfoText[] = 'errors rate: ' . round($errorRate, 2) . '%';
        }

        if ($jobEntity->errorsSinceLastSuccess > 0) {
            $jobInfoText[] = 'errors since last success:' . $jobEntity->errorsSinceLastSuccess;
        }

        return (!empty($jobInfoText))
            ? implode(' | ', $jobInfoText)
            : 'ok';
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return string
     */
    private function getOutputFormat(JobEntityInterface $jobEntity)
    {
        if ($jobEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE) {
            return '<info>%s</info>';
        }

        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Entity not of type ChronosJobEntity');
        }

        if ($jobEntity->errorsSinceLastSuccess > 0) {
            return '<fg=red>%s</>';
        }

        if ($jobEntity->errorCount > 0 || true === $jobEntity->disabled) {
            return '<comment>%s</comment>';
        }

        // else
        return '<info>%s</info>';
    }
}
