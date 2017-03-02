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
     * @param string $sProperty
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isValid($sProperty, JobEntityInterface $oJobEntity);

    /**
     * @return string
     */
    public function getLastErrorMessage();
}