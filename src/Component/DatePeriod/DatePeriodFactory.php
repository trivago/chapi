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
    private static $aIso8601Entity = [];

    /**
     * @param string $sIso8601
     * @return Iso8601Entity
     * @throws DatePeriodException
     */
    public function createIso8601Entity($sIso8601)
    {
        $_sKey = md5($sIso8601); // class cache key

        // return instance
        if (isset(self::$aIso8601Entity[$_sKey])) {
            return self::$aIso8601Entity[$_sKey];
        }

        // init instance
        try {
            return self::$aIso8601Entity[$_sKey] = new Iso8601Entity($sIso8601);
        } catch (\InvalidArgumentException $_oException) {
            throw new DatePeriodException(sprintf("Can't init Iso8601Entity for '%s' iso 8601 string.", $sIso8601), 1, $_oException);
        }
    }

    /**
     * @param $sIso8601
     * @param string $sTimeZone
     * @return \DatePeriod
     */
    public function createDatePeriod($sIso8601, $sTimeZone = '')
    {
        $_oIso8601Entity = $this->createIso8601Entity($sIso8601);

        if (!empty($sTimeZone)) {
            $_oDateStart = new \DateTime(str_replace('Z', '', $_oIso8601Entity->sStartTime), new \DateTimeZone($sTimeZone));
        } else {
            // todo: use a defined chronos time zone here?
            $_oDateStart = new \DateTime($_oIso8601Entity->sStartTime);
        }

        $_oDateInterval = new \DateInterval($_oIso8601Entity->sInterval);
        $_oDataEnd = new \DateTime();

        $_oDateStart->sub($_oDateInterval);
        $_oDataEnd->add($_oDateInterval);

        return new \DatePeriod($_oDateStart, $_oDateInterval, $_oDataEnd);
    }
}
