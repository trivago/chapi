<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Component\DatePeriod;

interface DatePeriodFactoryInterface
{
    const DIC_NAME = 'DatePeriodFactoryInterface';

    /**
     * @param $sIso8601
     * @return mixed
     */
    public function parseIso8601String($sIso8601);

    /**
     * @param $sIso8601
     * @param string $sTimeZone
     * @return \DatePeriod
     */
    public function createDatePeriod($sIso8601, $sTimeZone = '');
}