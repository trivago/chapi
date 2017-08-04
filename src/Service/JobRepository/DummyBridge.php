<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 14/02/17
 * Time: 14:24
 */

namespace Chapi\Service\JobRepository;

use Chapi\Entity\JobEntityInterface;
use Psr\Log\LoggerInterface;

class DummyBridge implements BridgeInterface
{

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        return [];
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $jobEntity)
    {
        $this->logger->warning('Adding a job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return false;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $jobEntity)
    {
        $this->logger->warning('Updating job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return false;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $jobEntity)
    {
        $this->logger->warning('Removing job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return false;
    }
}
