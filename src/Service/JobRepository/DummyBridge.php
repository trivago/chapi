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
    private $oLogger;

    public function __construct(
        LoggerInterface $oLogger
    )
    {
        $this->oLogger = $oLogger;
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        return [];
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        $this->oLogger->warning('Adding a job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return true;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        $this->oLogger->warning('Updating job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return true;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        $this->oLogger->warning('Removing job cannot be done. Required parameters missing or not configured properly in .chapiconfig');
        return true;
    }
}
