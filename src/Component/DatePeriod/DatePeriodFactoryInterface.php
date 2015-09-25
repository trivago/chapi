<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Component\DatePeriod;

use Chapi\Entity\DatePeriod\Iso8601Entity;
use Chapi\Exception\DatePeriodException;

interface DatePeriodFactoryInterface
{
    const DIC_NAME = 'DatePeriodFactoryInterface';

    /**
     * @param $sIso8601
     * @return mixed
     * @deprecated
     */
    public function parseIso8601String($sIso8601);

    /**
     * @param string $sIso8601
     * @return Iso8601Entity
     * @throws DatePeriodException
     */
    public function createIso8601Entity($sIso8601);

    /**
     * @param $sIso8601
     * @param string $sTimeZone
     * @return \DatePeriod
     */
    public function createDatePeriod($sIso8601, $sTimeZone = '');
}