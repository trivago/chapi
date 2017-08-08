<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 */


namespace Chapi\Service\JobRepository;

use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobRepository\Filter\JobFilterInterface;

class JobRepository implements JobRepositoryInterface
{

    /**
     * @var JobCollection
     */
    private $jobCollection;

    /**
     * @var BridgeInterface
     */
    private $repositoryBridge;

    /**
     * @var JobFilterInterface
     */
    private $jobFilter;

    /**
     * @param BridgeInterface $repositoryBridge
     * @param JobFilterInterface $jobFilter
     */
    public function __construct(
        BridgeInterface $repositoryBridge,
        JobFilterInterface $jobFilter
    ) {
        $this->repositoryBridge = $repositoryBridge;
        $this->jobFilter = $jobFilter;
    }

    /**
     * @param string $jobName
     * @return ChronosJobEntity
     */
    public function getJob($jobName)
    {
        $jobs = $this->getJobs();
        if (isset($jobs[$jobName])) {
            return $jobs[$jobName];
        }

        return null;
    }

    /**
     * @param string $jobName
     * @return bool
     */
    public function hasJob($jobName)
    {
        $jobs = $this->getJobs();
        return (isset($jobs[$jobName]));
    }

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs()
    {
        if (!is_null($this->jobCollection)) {
            return $this->jobCollection;
        }

        // apply filter
        $jobs = array_filter(
            $this->repositoryBridge->getJobs(),
            array($this->jobFilter, 'isInteresting')
        );

        return $this->jobCollection = new JobCollection(
            $jobs
        );
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $jobEntity)
    {
        if ($this->repositoryBridge->addJob($jobEntity)) {
            // if no collection inited the new job will init by chronos request
            if (!is_null($this->jobCollection)) {
                $this->jobCollection->offsetSet($jobEntity->getKey(), $jobEntity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity)
    {
        return $this->repositoryBridge->updateJob($jobEntity);
    }

    /**
     * @param string $jobName
     * @return bool
     */
    public function removeJob($jobName)
    {
        $jobEntity = $this->getJob($jobName);
        if (!$jobEntity) {
            throw new \InvalidArgumentException(sprintf('Can\'t remove unknown job "%s"', $jobName));
        }

        if ($this->repositoryBridge->removeJob($jobEntity)) {
            $this->jobCollection->offsetUnset($jobEntity->getKey());
            return true;
        }

        return false;
    }
}
