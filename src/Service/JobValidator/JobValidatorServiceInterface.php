<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobValidator;


use Chapi\Entity\Chronos\JobEntity;

interface JobValidatorServiceInterface
{
    const DIC_NAME = 'JobValidatorServiceInterface';

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntity $oJobEntity);

    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    public function validateJobEntity(JobEntity $oJobEntity);

    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntity $oJobEntity);
}