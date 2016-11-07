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

                case 'epsilon':
                    $_aValidProperties[$_sProperty] = $this->isEpsilonPropertyValid($oJobEntity);
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

                case 'constraints':
                    $_aValidProperties[$_sProperty] = $this->isConstraintsPropertyValid($mValue);
                    break;

                case 'container':
                    $_aValidProperties[$_sProperty] = $this->isContainerPropertyValid($mValue);
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
            catch (DatePeriodException $oException)
            {
                // invalid: Iso8601 is not valid and/or DatePeriodFactory is able to create a valid DatePeriod
            }
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    private function isEpsilonPropertyValid(JobEntity $oJobEntity)
    {
        if ($oJobEntity->isSchedulingJob() && !empty($oJobEntity->epsilon))
        {
            try
            {
                $_oDateIntervalEpsilon = new \DateInterval($oJobEntity->epsilon);
                $_iIntervalEpsilon = (int) $_oDateIntervalEpsilon->format('%Y%M%D%H%I%S');
                
                if ($_iIntervalEpsilon > 30) // if epsilon > "PT30S"
                {
                    $_oIso8601Entity = $this->oDatePeriodFactory->createIso8601Entity($oJobEntity->schedule);

                    $_oDateIntervalScheduling = new \DateInterval($_oIso8601Entity->sInterval);
                    $_iIntervalScheduling = (int) $_oDateIntervalScheduling->format('%Y%M%D%H%I%S');
                    
                    return ($_iIntervalScheduling > $_iIntervalEpsilon);
                }

                // if epsilon is less or equal than 30sec the not empty check is enough
                return true;
            }
            catch (\Exception $_oException)
            {
                // can't init \DateInterval instance
                return false;
            }
        }

        // else
        return (!empty($oJobEntity->epsilon));
    }

    /**
     * @param array $aConstraints
     * @return bool
     */
    private function isConstraintsPropertyValid(array $aConstraints)
    {
        if (!empty($aConstraints))
        {
            foreach ($aConstraints as $_aConstraint)
            {
                if (!is_array($_aConstraint) || count($_aConstraint) != 3)
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param JobEntity\ContainerEntity $oContainer
     * @return bool
     *
     * @see http://mesos.github.io/chronos/docs/api.html#adding-a-docker-job
     * This contains the subfields for the Docker container:
     *  type (required), image (required), forcePullImage (optional), network (optional),
     *  and volumes (optional)
     */
    private function isContainerPropertyValid($oContainer)
    {
        if (is_null($oContainer))
        {
            return true;
        }

        if (is_object($oContainer))
        {
            if (empty($oContainer->type) || empty($oContainer->image))
            {
                return false;
            }
            
            if (!is_null($oContainer->volumes) && !is_array($oContainer->volumes))
            {
                return false;
            }

            foreach ($oContainer->volumes as $_oVolume)
            {
                if (!in_array($_oVolume->mode, ['RO', 'RW']))
                {
                    return false;
                }
            }

            return true;
        }
        
        return false;
    }
}