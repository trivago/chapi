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
use Chapi\Entity\JobEntityInterface;

interface JobValidatorServiceInterface
{
    const DIC_NAME = 'JobValidatorServiceInterface';

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntityInterface $oJobEntity);

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntityInterface $oJobEntity);
}
