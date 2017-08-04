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
        $force = (bool) $this->input->getOption('force');
        $jobNames = $this->input->getArgument('jobnames');

        /** @var StoreJobBusinessCaseFactoryInterface $storeJobBusinessCaseFactory */
        $storeJobBusinessCaseFactory = $this->getContainer()->get(StoreJobBusinessCaseFactoryInterface::DIC_NAME);

        if (count($jobNames) == 0) {
            $storeJobBusinessCases = $storeJobBusinessCaseFactory->getAllStoreJobBusinessCase();
            /** @var StoreJobBusinessCaseInterface $storeJobBusinessCase */
            foreach ($storeJobBusinessCases as $storeJobBusinessCase) {
                $storeJobBusinessCase->storeJobsToLocalRepository($jobNames, $force);
            }
            return 0;
        }


        foreach ($jobNames as $jobName) {
            // since the job can be of any underlying system
            // we get teh businesscase for the system
            // and the update it there.

            /** @var StoreJobBusinessCaseInterface  $_oStoreJobBusinessCase */
            $storeJobBusinessCase = $storeJobBusinessCaseFactory->getBusinessCaseWithJob($jobName);
            if (null == $storeJobBusinessCase) {
                // not found but process the rest of the jobs
                continue;
            }

            // TODO: add method for single job to LocalRepository update
            $storeJobBusinessCase->storeJobsToLocalRepository(array($jobName), $force);
        }

        return 0;
    }
}
