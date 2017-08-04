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
use Chapi\Entity\Chronos\ChronosJobEntity;
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
    private $datePeriodFactory;

    /**
     * Epsilon constructor.
     * @param DatePeriodFactoryInterface $datePeriodFactory
     */
    public function __construct(DatePeriodFactoryInterface $datePeriodFactory)
    {
        $this->datePeriodFactory = $datePeriodFactory;
    }
    
    /**
     * @inheritDoc
     */
    public function isValid($property, JobEntityInterface $jobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isSchedulePropertyValid($jobEntity),
            $property,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function isSchedulePropertyValid(JobEntityInterface $jobEntity)
    {
        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Required ChronosJobEntity. Something else found.');
        }

        if (empty($jobEntity->schedule) && !empty($jobEntity->parents)) {
            return true;
        }

        if (!empty($jobEntity->schedule) && empty($jobEntity->parents)) {
            try {
                $datePeriod = $this->datePeriodFactory->createDatePeriod($jobEntity->schedule, $jobEntity->scheduleTimeZone);
                return (false !== $datePeriod);
            } catch (\Exception $exception) {
                // invalid: Iso8601 is not valid and/or DatePeriodFactory is able to create a valid DatePeriod
            }
        }

        return false;
    }
}
