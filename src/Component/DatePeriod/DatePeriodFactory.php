<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Component\DatePeriod;

use Chapi\Exception\DatePeriodException;

class DatePeriodFactory implements DatePeriodFactoryInterface
{
    const REG_EX_ISO_8601_STRING = '#(R[0-9]*)/(.*)/(P.*)#';

    /**
     * @param $sIso8601
     * @return mixed
     * @throws DatePeriodException
     */
    public function parseIso8601String($sIso8601)
    {
        $aMatch = [];
        preg_match(self::REG_EX_ISO_8601_STRING, $sIso8601, $aMatch);

        if (count($aMatch) != 4)
        {
            throw new DatePeriodException(sprintf("Can't parse '%s' as iso 8601 string.", $sIso8601));
        }

        return $aMatch;
    }

    /**
     * @param $sIso8601
     * @param string $sTimeZone
     * @return \DatePeriod
     */
    public function createDatePeriod($sIso8601, $sTimeZone = '')
    {
        $aMatch = $this->parseIso8601String($sIso8601);


        if (!empty($sTimeZone))
        {
            $_oDateStart = new \DateTime(str_replace('Z', '', $aMatch[2]));
            $_oDateStart->setTimezone(new \DateTimeZone($sTimeZone));
        }
        else
        {
            $_oDateStart = new \DateTime($aMatch[2]);
        }

        $_oDateInterval = new \DateInterval($aMatch[3]);
        $_oDataEnd = new \DateTime();
        $_oDataEnd->add($_oDateInterval);

        return new \DatePeriod($_oDateStart, $_oDateInterval, $_oDataEnd);
    }
}