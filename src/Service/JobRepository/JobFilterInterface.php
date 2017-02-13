<?php

namespace Chapi\Service\JobRepository;


use Chapi\Entity\JobEntityInterface;

interface JobFilterInterface
{
    const DIC_NAME_FILTER_CHRONOS_ENTITY = 'FilterForChronosEntity';
    const DIC_NAME_FILTER_MARATHON_ENTITY = 'FilterForMarathonEntity';

    /**
     * Returns true if the job is of particular entity
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isInteresting(JobEntityInterface $oJobEntity);
}