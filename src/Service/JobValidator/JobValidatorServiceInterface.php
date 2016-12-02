<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobValidator;


use Chapi\Entity\Chronos\ChronosJobEntity;

interface JobValidatorServiceInterface
{
    const DIC_NAME = 'JobValidatorServiceInterface';

    /**
     * @param ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function isEntityValid(ChronosJobEntity $oJobEntity);

    /**
     * @param ChronosJobEntity $oJobEntity
     * @return array
     */

    public function validateJobEntity(ChronosJobEntity $oJobEntity);

    /**
     * @param ChronosJobEntity $oJobEntity
     * @return array
     */
    public function getInvalidProperties(ChronosJobEntity $oJobEntity);
}