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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddJobCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('Add a job to chronos')
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
//        $_oJobService->addJob();
        var_dump(
            $_oJobService->getJobs()
        );

    }
}