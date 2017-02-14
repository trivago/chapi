<?php
/**
 * Created by PhpStorm.
 * User: bthapaliya
 * Date: 14/02/17
 * Time: 14:24
 */

namespace Chapi\Service\JobRepository;


use Chapi\Entity\JobEntityInterface;

class DummyBridge implements BridgeInterface
{

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
        return true;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        return true;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        return true;
    }
}