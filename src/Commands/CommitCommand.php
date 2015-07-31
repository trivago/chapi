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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        /** @var StoreJobBusinessCaseInterface  $_oStoreJobBusinessCase */
        $_oStoreJobBusinessCase = $this->getContainer()->get(StoreJobBusinessCaseInterface::DIC_NAME);

        $_oStoreJobBusinessCase->storeIndexedJobs();

        return 0;
    }


}