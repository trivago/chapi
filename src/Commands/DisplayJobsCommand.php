<?php
/**
 * @package: orchestra-
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 * @link:    http://
 */


namespace Chapi\Commands;


use Chapi\Service\Chronos\JobServiceInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayJobsCommand extends AbstractCommand
{
    const DEFAULT_VALUE_JOB_NAME = 'all';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('display')
            ->setDescription('Display your job(s) and filter they by status')
            ->addArgument('jobName', InputArgument::OPTIONAL, 'display a specific job', self::DEFAULT_VALUE_JOB_NAME)
            ->addOption('onlyFailed', 'f', InputOption::VALUE_OPTIONAL, 'Display only failed jobs', false)
        ;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        if (!$this->isAppRunable($oOutput))
        {
            exit(1);
        }

        /** @var JobServiceInterface  $_oJobService */
        $_oJobService = $this->getContainer()->get(JobServiceInterface::DIC_NAME);

        $_sJobName = $oInput->getArgument('jobName');
        $_bOnlyFailed = (bool) $oInput->getOption('onlyFailed');

        if (!empty($_sJobName) && $_sJobName != self::DEFAULT_VALUE_JOB_NAME)
        {
            $_aJobData = $_oJobService->getJob($_sJobName);
            foreach ($_aJobData as $_sKey => $_sValue)
            {
                if (is_array($_sValue))
                {
                    $_sValue = implode(' | ', $_sValue);
                }
                $oOutput->writeln(sprintf('<comment>%s:</comment> <info>%s</info>', $_sKey, $_sValue));
            }

        }
        else
        {
            foreach ($_oJobService->getJobs() as $_aJobData)
            {
                if (
                    ($_bOnlyFailed && $_aJobData['errorsSinceLastSuccess'] > 0)
                    || $_bOnlyFailed == false
                )
                {
                    $oOutput->writeln(sprintf('<comment>%s</comment>', $_aJobData['name']));
                }
            }

        }
    }
}