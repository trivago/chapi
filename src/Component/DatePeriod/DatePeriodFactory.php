<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Component\DatePeriod;

use Chapi\Entity\DatePeriod\Iso8601Entity;
use Chapi\Exception\DatePeriodException;

class DatePeriodFactory implements DatePeriodFactoryInterface
{
    /**
     * @var Iso8601Entity[]
     */
    private static $iso8601Entity = [];

    /**
     * @param string $iso8601
     * @return Iso8601Entity
     * @throws DatePeriodException
     */
    public function createIso8601Entity($iso8601)
    {
        $key = md5($iso8601); // class cache key

        // return instance
        if (isset(self::$iso8601Entity[$key])) {
            return self::$iso8601Entity[$key];
        }

        // init instance
        try {
            return self::$iso8601Entity[$key] = new Iso8601Entity($iso8601);
        } catch (\InvalidArgumentException $exception) {
            throw new DatePeriodException(sprintf("Can't init Iso8601Entity for '%s' iso 8601 string.", $iso8601), 1, $exception);
        }
    }

    /**
     * @param $iso8601
     * @param string $timezone
     * @return \DatePeriod
     */
    public function createDatePeriod($iso8601, $timezone = '')
    {
        $iso8601Entity = $this->createIso8601Entity($iso8601);

        if (!empty($timezone)) {
            $dateStart = new \DateTime(str_replace('Z', '', $iso8601Entity->startTime), new \DateTimeZone($timezone));
        } else {
            // todo: use a defined chronos time zone here?
            $dateStart = new \DateTime($iso8601Entity->startTime);
        }

        $dateInterval = new \DateInterval($iso8601Entity->interval);
        $dateEnd = new \DateTime();

        $dateStart->sub($dateInterval);
        $dateEnd->add($dateInterval);

        return new \DatePeriod($dateStart, $dateInterval, $dateEnd);
    }
}
