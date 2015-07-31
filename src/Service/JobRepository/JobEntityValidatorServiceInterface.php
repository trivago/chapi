<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\JobEntity;

interface JobEntityValidatorServiceInterface
{
    const DIC_NAME = 'JobEntityValidatorServiceInterface';

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntity $oJobEntity);
}