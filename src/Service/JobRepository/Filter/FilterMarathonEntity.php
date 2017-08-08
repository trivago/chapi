<?php

namespace Chapi\Service\JobRepository\Filter;

use Chapi\Entity\JobEntityInterface;

class FilterMarathonEntity implements JobFilterInterface
{

    /**
     * Returns true if the job is of particular entity
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $jobEntity)
    {
        return $jobEntity->getEntityType() == JobEntityInterface::MARATHON_TYPE;
    }
}
