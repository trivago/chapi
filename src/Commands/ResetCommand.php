<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Commands;


use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('reset')
            ->setDescription('Remove jobs from the index')
            ->addArgument('jobnames', InputArgument::REQUIRED, 'Jobs to remove from the index')
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
    protected function process()
    {
        /** @var JobIndexServiceInterface  $_oJobIndexService */
        $_oJobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
        $_sJobNames = $this->oInput->getArgument('jobnames');

        if (in_array($_sJobNames, array('.', '*')))
        {
            $_oJobIndexService->resetJobIndex();
            return 0;
        }

        $_aJobNames = explode(' ', $_sJobNames);
        $_oJobIndexService->removeJobs($_aJobNames);

        return 0;
    }
}