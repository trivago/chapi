<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Service\JobRepository;

use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Exception\DatePeriodException;

class JobEntityValidatorService implements JobEntityValidatorServiceInterface
{
    const REG_EX_VALID_NAME = '/^[a-zA-Z0-9_-]*$/';

    /**
     * @var DatePeriodFactoryInterface
     */
    private $oDatePeriodFactory;

    /**
     * @param DatePeriodFactoryInterface $oDatePeriodFactory
     */
    public function __construct(
        DatePeriodFactoryInterface $oDatePeriodFactory
    )
    {
        $this->oDatePeriodFactory = $oDatePeriodFactory;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntity $oJobEntity)
    {
        return (!in_array(false, $this->validateJobEntity($oJobEntity)));
    }

    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    public function validateJobEntity(JobEntity $oJobEntity)
    {
        $_aValidProperties = [];

        foreach ($oJobEntity as $_sProperty => $mValue)
        {
            switch ($_sProperty)
            {
                case 'name':
                    $_aValidProperties[$_sProperty] = $this->isNamePropertyValid($mValue);
                    break;

                case 'command':
                case 'description':
                case 'owner':
                case 'ownerName':
                    $_aValidProperties[$_sProperty] = (!empty($oJobEntity->{$_sProperty}));
                    break;

                case 'async':
                case 'disabled':
                case 'softError':
                case 'highPriority':
                    $_aValidProperties[$_sProperty] = (is_bool($oJobEntity->{$_sProperty}));
                    break;

                case 'schedule':
                    $_aValidProperties[$_sProperty] = $this->isSchedulePropertyValid($oJobEntity);
                    break;

                case 'parents':
                    $_aValidProperties[$_sProperty] = (is_array($oJobEntity->{$_sProperty}));
                    break;

                case 'retries':
                    $_aValidProperties[$_sProperty] = ($oJobEntity->{$_sProperty} >= 0);
                    break;
            }
        }

        return $_aValidProperties;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntity $oJobEntity)
    {
        $_aValidationFields = $this->validateJobEntity($oJobEntity);

        $_aInvalidFields = [];
        foreach ($_aValidationFields as $_sProperty => $_bIsValid)
        {
            if (false == $_bIsValid)
            {
                $_aInvalidFields[] = $_sProperty;
            }
        }

        return $_aInvalidFields;
    }

    /**
     * @param string $sName
     * @return bool
     */
    private function isNamePropertyValid($sName)
    {
        return (!empty($sName) && preg_match(self::REG_EX_VALID_NAME, $sName));
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    private function isSchedulePropertyValid(JobEntity $oJobEntity)
    {
        if (empty($oJobEntity->schedule) && !empty($oJobEntity->parents))
        {
            return true;
        }

        if (!empty($oJobEntity->schedule) && empty($oJobEntity->parents))
        {
            try
            {
                $_oDataPeriod = $this->oDatePeriodFactory->createDatePeriod($oJobEntity->schedule, $oJobEntity->scheduleTimeZone);
                return (false !== $_oDataPeriod);
            }
            catch(DatePeriodException $oException)
            {
            }
        }

        return false;
    }
}