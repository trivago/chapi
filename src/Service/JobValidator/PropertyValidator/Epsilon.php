<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 */

namespace Chapi\Service\JobValidator\PropertyValidator;


use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Epsilon extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'EpsilonValidator';
    const MESSAGE_TEMPLATE = '"%s" is not ISO8601 conform not less than the time period';

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
    public function isValid($sProperty, JobEntity $oJobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isEpsilonPropertyValid($oJobEntity),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
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
}