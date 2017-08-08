<?php
/**
 * @package: cahpi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */

namespace Chapi\Service\JobValidator;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;

interface PropertyValidatorInterface
{
    /**
     * @param string $property
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isValid($property, JobEntityInterface $jobEntity);

    /**
     * @return string
     */
    public function getLastErrorMessage();
}
