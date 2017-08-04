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
     * @param string $iso8601
     * @return Iso8601Entity
     * @throws DatePeriodException
     */
    public function createIso8601Entity($iso8601);

    /**
     * @param $iso8601
     * @param string $timezone
     * @return \DatePeriod
     */
    public function createDatePeriod($iso8601, $timezone = '');
}
