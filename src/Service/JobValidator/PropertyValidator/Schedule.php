<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */

namespace Chapi\Service\JobValidator\PropertyValidator;


use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Schedule extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'ScheduleValidator';
    const MESSAGE_TEMPLATE = '"%s" is not a valid ISO8601 string and/or DatePeriodFactory is not able to create a valid DatePeriod';

    /**
     * @var DatePeriodFactoryInterface
     */
    private $oDatePeriodFactory;

    /**
     * Epsilon constructor.
     * @param DatePeriodFactoryInterface $oDatePeriodFactory
     */
    public function __construct(DatePeriodFactoryInterface $oDatePeriodFactory)
    {
        $this->oDatePeriodFactory = $oDatePeriodFactory;
    }
    
    /**
     * @inheritDoc
     */
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isSchedulePropertyValid($oJobEntity),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    private function isSchedulePropertyValid(JobEntityInterface $oJobEntity)
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
            catch (\Exception $oException)
            {
                // invalid: Iso8601 is not valid and/or DatePeriodFactory is able to create a valid DatePeriod
            }
        }

        return false;
    }
}