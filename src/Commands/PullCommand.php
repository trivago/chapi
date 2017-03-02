<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-02
 *
 */


namespace Chapi\Commands;


use Chapi\BusinessCase\JobManagement\StoreJobBusinessCaseFactoryInterface;
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

        /** @var StoreJobBusinessCaseFactoryInterface $_oStoreJobBusinessCaseFactory */
        $_oStoreJobBusinessCaseFactory = $this->getContainer()->get(StoreJobBusinessCaseFactoryInterface::DIC_NAME);

        if (count($_aJobNames) == 0)
        {
            $_aStoreJobBusinessCases = $_oStoreJobBusinessCaseFactory->getAllStoreJobBusinessCase();
            /** @var StoreJobBusinessCaseInterface $_oStoreJobBusinessCase */
            foreach ($_aStoreJobBusinessCases as $_oStoreJobBusinessCase)
            {
                $_oStoreJobBusinessCase->storeJobsToLocalRepository($_aJobNames, $_bForce);
            }
            return 0;
        }


        foreach ($_aJobNames as $_sJobName)
        {
            // since the job can be of any underlying system
            // we get teh businesscase for the system
            // and the update it there.

            /** @var StoreJobBusinessCaseInterface  $_oStoreJobBusinessCase */
            $_oStoreJobBusinessCase = $_oStoreJobBusinessCaseFactory->getBusinessCaseWithJob($_sJobName);
            if (null == $_oStoreJobBusinessCase)
            {
                // not found but process the rest of the jobs
                continue;
            }

            // TODO: add method for single job to LocalRepository update
            $_oStoreJobBusinessCase->storeJobsToLocalRepository(array($_sJobName), $_bForce);

        }

        return 0;
    }
}