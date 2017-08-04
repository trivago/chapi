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
use Chapi\Service\JobRepository\JobRepositoryInterface;
use Chapi\Service\JobValidator\JobValidatorServiceInterface;

class ValidationCommand extends AbstractCommand
{

    /**
     * @var array[]
     */
    private $invalidJobs = [];

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
        $jobNames = JobUtils::getJobNames($this->input, $this);
        $jobsToValidate = (JobUtils::isWildcard($jobNames))
            ? $this->getLocalJobs()
            : $jobNames
        ;

        if ($this->hasInvalidJobs($jobsToValidate)) {
            $this->output->writeln("<comment>Found invalid jobs:</comment>\n");

            foreach ($this->getInvalidJobsByJobNames($jobsToValidate) as $jobName => $invalidProperties) {
                $this->printInvalidJobProperties($jobName, $invalidProperties);
            }

            return 1;
        }

        //else
        $this->output->writeln('<info>All checked jobs look valid</info>');
        return 0;
    }

    /**
     * @param string[] $jobs
     * @return bool
     */
    private function hasInvalidJobs(array $jobs)
    {
        $invalidJobs = $this->getInvalidJobsByJobNames($jobs);
        return (count($invalidJobs) > 0);
    }

    /**
     * @param array $jobs
     * @return array
     */
    private function getInvalidJobsByJobNames(array $jobs)
    {
        $key = md5(implode('.', $jobs));

        if (isset($this->invalidJobs[$key])) {
            return $this->invalidJobs[$key];
        }

        $invalidJobs = [];

        /** @var JobValidatorServiceInterface $jobEntityValidationService */
        $jobEntityValidationService = $this->getContainer()->get(JobValidatorServiceInterface::DIC_NAME);

        /** @var JobRepositoryInterface  $jobRepositoryLocal */
        $jobRepositoryLocal = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_FILESYSTEM_CHRONOS);

        foreach ($jobs as $jobName) {
            $jobEntity = $jobRepositoryLocal->getJob($jobName);

            if (false === $jobEntityValidationService->isEntityValid($jobEntity)) {
                $invalidJobs[$jobName] = $jobEntityValidationService->getInvalidProperties($jobEntity);
            }
        }

        return $this->invalidJobs[$key] = $invalidJobs;
    }

    /**
     * @param string $jobName
     * @param string[] $invalidProperties
     */
    private function printInvalidJobProperties($jobName, array $invalidProperties)
    {
        $formatJobName = "\t<fg=red>%s:</>";
        $formatErrorMessage = "\t\t<fg=red>%s</>";

        $this->output->writeln(sprintf($formatJobName, $jobName));
        foreach ($invalidProperties as $errorMessage) {
            $this->output->writeln(sprintf($formatErrorMessage, $errorMessage));
        }
    }

    /**
     * @return string[]
     */
    private function getLocalJobs()
    {
        $jobNames = [];

        /** @var JobRepositoryInterface  $jobRepositoryLocal */
        $jobRepositoryLocal = $this->getContainer()->get(JobRepositoryInterface::DIC_NAME_FILESYSTEM_CHRONOS);

        /** @var ChronosJobEntity $jobEntity */
        foreach ($jobRepositoryLocal->getJobs() as $jobEntity) {
            $jobNames[] = $jobEntity->name;
        }

        return $jobNames;
    }
}
