<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-02
 *
 */


namespace Chapi\Commands;


use Chapi\BusinessCase\JobManagement\StoreJobBusinessCaseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class PullCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('pull')
            ->setDescription('Pull jobs from chronos and add them to local repository')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to overwrite local jobs')
            ->addArgument('jobnames', InputArgument::IS_ARRAY, 'Jobnames to pull')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $_bForce = (bool) $this->oInput->getOption('force');
        $_aJobNames = $this->oInput->getArgument('jobnames');

        /** @var StoreJobBusinessCaseInterface  $_oStoreJobBusinessCase */
        $_oStoreJobBusinessCase = $this->getContainer()->get(StoreJobBusinessCaseInterface::DIC_NAME);

        $_oStoreJobBusinessCase->storeJobsToLocalRepository($_aJobNames, $_bForce);

        return 0;
    }
}