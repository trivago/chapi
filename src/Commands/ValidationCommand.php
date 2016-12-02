<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-09-20
 *
 * @link:    http://
 */

namespace Chapi\Commands;

use Chapi\Commands\AbstractCommand;
use Chapi\Component\Command\JobUtils;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Service\JobRepository\JobEntityValidatorServiceInterface;
use Chapi\Service\JobRepository\JobRepositoryInterface;

class ValidationCommand extends AbstractCommand
{

    /**
     * @var array[]
     */
    private $aInvalidJobs = [];

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('validate')
            ->setDescription('Validate local jobs')
        ;

        JobUtils::configureJobNamesArgument($this, 'Jobs to validate');
    }

    /**
     * @inheritDoc
     */
    protected function process()
    {
        $_aJobNames = JobUtils::getJobNames($this->oInput, $this);
        $_aJobsToValidate = (JobUtils::isWildcard($_aJobNames))
            ? $this->getLocalJobs()
            : $_aJobNames
        ;

        if ($this->hasInvalidJobs($_aJobsToValidate))
        {
            $this->oOutput->writeln("<comment>Found invalid jobs:</comment>\n");

            foreach ($this->getInvalidJobsByJobNames($_aJobsToValidate) as $_sJobName => $_aInvalidProperties)
            {
                $this->printInvalidJobProperties($_sJobName, $_aInvalidProperties);
            }

            return 1;
        }

        //else
        $this->oOutput->writeln('<info>All checked jobs look valid</info>');
        return 0;
    }

    /**
     * @param string[] $aJobs
     * @return bool
     */
    private function hasInvalidJobs(array $aJobs)
    {
        $_aInvalidJobs = $this->getInvalidJobsByJobNames($aJobs);
        return (count($_aInvalidJobs) > 0);
    }

    /**
     * @param array $aJobs
     * @return array
     */
    private function getInvalidJobsByJobNames(array $aJobs)
    {
        $_sKey = md5(implode('.', $aJobs));

        if (isset($this->aInvalidJobs[$_sKey]))
        {
            return $this->aInvalidJobs[$_sKey];
        }

        $_aInvalidJobs = [];

        /** @var JobValidatorServiceInterface $_oJobEntityValidationService */
        $_oJobEntityValidationService = $this->getContainer()->get(JobValidatorServiceInterface::DIC_NAME);

        /** @var JobRepositoryInterface  $_oJobRepositoryLocale */
        $_oJobRepositoryLocale = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_FILESYSTEM);

        foreach ($aJobs as $_sJobName)
        {
            $_oJobEntity = $_oJobRepositoryLocale->getJob($_sJobName);

            if (false === $_oJobEntityValidationService->isEntityValid($_oJobEntity))
            {
                $_aInvalidJobs[$_sJobName] = $_oJobEntityValidationService->getInvalidProperties($_oJobEntity);
            }
        }

        return $this->aInvalidJobs[$_sKey] = $_aInvalidJobs;
    }

    /**
     * @param string $sJobName
     * @param string[] $aInvalidProperties
     */
    private function printInvalidJobProperties($sJobName, array $aInvalidProperties)
    {
        $_sFormatJobName = "\t<fg=red>%s:</>";
        $_sFormatErrMsg = "\t\t<fg=red>%s</>";

        $this->oOutput->writeln(sprintf($_sFormatJobName, $sJobName));
        foreach ($aInvalidProperties as $_sErrorMessage)
        {
            $this->oOutput->writeln(sprintf($_sFormatErrMsg, $_sErrorMessage));
        }
    }

    /**
     * @return string[]
     */
    private function getLocalJobs()
    {
        $_aJobNames = [];

        /** @var JobRepositoryInterface  $_oJobRepositoryLocale */
        $_oJobRepositoryLocale = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_FILESYSTEM);

        /** @var ChronosJobEntity $_oJobEntity */
        foreach ($_oJobRepositoryLocale->getJobs() as $_oJobEntity)
        {
            $_aJobNames[] = $_oJobEntity->name;
        }

        return $_aJobNames;
    }
}