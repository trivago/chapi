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

interface PropertyValidatorInterface
{
    /**
     * @param string $sProperty
     * @param JobEntity $oJobEntity
     * @return boolean
     */
    public function isValid($sProperty, JobEntity $oJobEntity);

    /**
     * @return string
     */
    public function getLastErrorMessage();
}