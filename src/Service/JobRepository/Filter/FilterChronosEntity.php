<?php

namespace Chapi\Service\JobRepository\Filter;

use Chapi\Entity\JobEntityInterface;

class FilterChronosEntity implements JobFilterInterface
{

    /**
     * Returns true if the job is of particular entity
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $jobEntity)
    {
        return $jobEntity->getEntityType() == JobEntityInterface::CHRONOS_TYPE;
    }
}
