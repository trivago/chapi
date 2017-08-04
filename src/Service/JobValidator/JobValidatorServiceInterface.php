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
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return bool
     */
    public function isEntityValid(JobEntityInterface $jobEntity);

    /**
     * @param ChronosJobEntity|JobEntityInterface $jobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntityInterface $jobEntity);
}
