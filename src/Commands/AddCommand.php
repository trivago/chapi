<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Commands;


use Chapi\BusinessCase\Comparison\JobComparisonInterface;
use Chapi\Service\JobIndex\JobIndexServiceInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('add')
            ->setDescription('Add job contents to the index')
            ->addArgument('jobnames', InputArgument::IS_ARRAY, 'Jobs to add to the index')
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
        $_aJobNames = $this->oInput->getArgument('jobnames');

        if (empty($_aJobNames))
        {
            throw new \InvalidArgumentException('Nothing specified, nothing added. Maybe you wanted to say "add ."?');
        }

        if (in_array($_aJobNames[0], array('.', '*')))
        {
            $_oJobIndexService->addJobs($this->getChangedJobs());
            return 0;
        }

        $_oJobIndexService->addJobs($_aJobNames);

        return 0;
    }

    /**
     * @return array
     */
    private function getChangedJobs()
    {
        // job data
        /** @var JobComparisonInterface  $_oJobComparisonBusinessCase */
        $_oJobComparisonBusinessCase = $this->getContainer()->get(JobComparisonInterface::DIC_NAME);

        $_aNewJobs = $_oJobComparisonBusinessCase->getChronosMissingJobs();
        $_aMissingJobs = $_oJobComparisonBusinessCase->getLocalMissingJobs();
        $_aLocalJobUpdates = $_oJobComparisonBusinessCase->getLocalJobUpdates();

        return array_merge($_aNewJobs, $_aMissingJobs, $_aLocalJobUpdates);
    }
}