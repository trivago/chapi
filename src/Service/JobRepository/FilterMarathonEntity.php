<?php

namespace Chapi\Service\JobRepository;


use Chapi\Entity\JobEntityInterface;

class FilterMarathonEntity implements JobFilterInterface
{

    /**
     * Returns true if the job is of particular entity
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $oJobEntity)
    {
        if ($oJobEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE) {
            return true;
        }
        return false;
    }
}