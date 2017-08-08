<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Commands;

use Chapi\BusinessCase\JobManagement\StoreJobBusinessCaseFactoryInterface;
use Chapi\BusinessCase\JobManagement\StoreJobBusinessCaseInterface;

class CommitCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('commit')
            ->setDescription('Record changes to chronos/marathon')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var StoreJobBusinessCaseFactoryInterface $storeJobBUsinessCaseFactory */
        $storeJobBUsinessCaseFactory = $this->getContainer()->get(StoreJobBusinessCaseFactoryInterface::DIC_NAME);

        $storeJobBusinessCases = $storeJobBUsinessCaseFactory->getAllStoreJobBusinessCase();

        /** @var StoreJobBusinessCaseInterface $storeJobBusinessCase */
        foreach ($storeJobBusinessCases as $storeJobBusinessCase) {
            $storeJobBusinessCase->storeIndexedJobs();
        }

        return 0;
    }
}
