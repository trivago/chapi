<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 */

namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Epsilon extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'EpsilonValidator';
    const MESSAGE_TEMPLATE = '"%s" is not ISO8601 conform not less than the time period';

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
            $this->isEpsilonPropertyValid($jobEntity),
            $property,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function isEpsilonPropertyValid(JobEntityInterface $jobEntity)
    {
        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Required ChronosJobEntity. Something else found.');
        }

        if ($jobEntity->isSchedulingJob() && !empty($jobEntity->epsilon)) {
            try {
                $dateIntervalEpsilon = new \DateInterval($jobEntity->epsilon);
                $intervalEpsilon = (int) $dateIntervalEpsilon->format('%Y%M%D%H%I%S');

                if ($intervalEpsilon > 30) { // if epsilon > "PT30S"
                    $iso8601Entity = $this->datePeriodFactory->createIso8601Entity($jobEntity->schedule);

                    $dateIntervalScheduling = new \DateInterval($iso8601Entity->interval);
                    $intervalScheduling = (int) $dateIntervalScheduling->format('%Y%M%D%H%I%S');

                    return ($intervalScheduling > $intervalEpsilon);
                }

                // if epsilon is less or equal than 30sec the not empty check is enough
                return true;
            } catch (\Exception $exception) {
                // can't init \DateInterval instance
                return false;
            }
        }

        // else
        return (!empty($jobEntity->epsilon));
    }
}
