<?php
/**
 * @package: chapi
 *
 * @author:  bthapaliya
 * @since:   2016-12-02
 */

namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

class BridgeMarathon implements BridgeInterface
{
    /**
     * @inheritdoc
     */
    public function getJobs()
    {
        return [];
    }

    public function addJob(JobEntityInterface $oJobEntity)
    {

    }

    public function updateJob(JobEntityInterface $oJobEntity)
    {

    }

    public function removeJob(JobEntityInterface $oJobEntity)
    {

    }

}