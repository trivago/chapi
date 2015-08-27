<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Commands;

use Chapi\BusinessCase\JobManagement\StoreJobBusinessCaseInterface;

class CommitCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('commit')
            ->setDescription('Record changes to chronos')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var StoreJobBusinessCaseInterface  $_oStoreJobBusinessCase */
        $_oStoreJobBusinessCase = $this->getContainer()->get(StoreJobBusinessCaseInterface::DIC_NAME);

        $_oStoreJobBusinessCase->storeIndexedJobs();

        return 0;
    }


}