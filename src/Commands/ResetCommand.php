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

class ResetCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('reset')
            ->setDescription('Remove jobs from the index')
            ->addArgument('jobnames', InputArgument::IS_ARRAY, 'Jobs to remove from the index')
        ;
    }

    /**
     * @return int
     */
    protected function process()
    {
        /** @var JobIndexServiceInterface  $_oJobIndexService */
        $_oJobIndexService = $this->getContainer()->get(JobIndexServiceInterface::DIC_NAME);
        $_aJobNames = $this->oInput->getArgument('jobnames');

        if (empty($_aJobNames))
        {
            throw new \InvalidArgumentException('Nothing specified, nothing resetted. Maybe you wanted to say "reset ."?');
        }

        if (in_array($_aJobNames[0], array('.', '*')))
        {
            $_oJobIndexService->resetJobIndex();
            return 0;
        }

        $_oJobIndexService->removeJobs($_aJobNames);

        return 0;
    }
}