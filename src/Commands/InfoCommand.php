<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-30
 *
 */

namespace Chapi\Commands;

use Chapi\Service\JobRepository\JobRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

class InfoCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('info')
            ->setDescription('Display your job information from chronos')
            ->addArgument('jobName', InputArgument::REQUIRED, 'selected job')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $jobName = $this->input->getArgument('jobName');

        $chronosJobEntity = $this->checkInChronos($jobName);
        $jobEntity = $chronosJobEntity == null ? $this->checkInMarathon($jobName) : $chronosJobEntity;

        if (!$jobEntity) {
            $this->output->writeln(sprintf('<fg=red>%s</>', 'Could not find the job.'));
            return 1;
        }

        $this->output->writeln(sprintf("\n<comment>info '%s'</comment>\n", $jobEntity->getKey()));

        $table = new Table($this->output);
        $table->setHeaders(array('Property', 'Value'));

        foreach ($jobEntity as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $emptyString = (is_object($value)) ? '{ }' : '[ ]';

                $value = (!empty($value))
                    ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : $emptyString;
            } elseif (is_bool($value)) {
                $value = (true === $value)
                    ? 'true'
                    : 'false';
            }

            $table->addRow(array($key, $value));
        }

        $table->render();

        return 0;
    }

    private function checkInChronos($jobName)
    {
        /** @var JobRepositoryInterface  $jobRepositoryChronos */
        $jobRepositoryChronos = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_CHRONOS);
        return $jobRepositoryChronos->getJob($jobName);
    }

    private function checkInMarathon($jobName)
    {
        /** @var JobRepositoryInterface  $jobRepositoryMarathon */
        $jobRepositoryMarathon = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_MARATHON);
        return $jobRepositoryMarathon->getJob($jobName);
    }
}
