<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-12
 *
 */

namespace unit\Component\DatePeriod;

use Chapi\Component\DatePeriod\DatePeriodFactory;

class DatePeriodFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testParseIso8601StringSuccess()
    {
        $datePeriodFactory = new DatePeriodFactory();

        $iso8601Entity = $datePeriodFactory->createIso8601Entity('R/2015-07-07T01:00:00Z/P1D');

        $this->assertEquals('R/2015-07-07T01:00:00Z/P1D', $iso8601Entity->iso8601);
        $this->assertEquals('R', $iso8601Entity->repeat);
        $this->assertEquals('2015-07-07T01:00:00Z', $iso8601Entity->startTime);
        $this->assertEquals('P1D', $iso8601Entity->interval);


        $iso8601Entity = $datePeriodFactory->createIso8601Entity('R0/2015-07-07T01:00:00Z/PT1M');

        $this->assertEquals('R0/2015-07-07T01:00:00Z/PT1M', $iso8601Entity->iso8601);
        $this->assertEquals('R0', $iso8601Entity->repeat);
        $this->assertEquals('2015-07-07T01:00:00Z', $iso8601Entity->startTime);
        $this->assertEquals('PT1M', $iso8601Entity->interval);
    }

    /**
     * @expectedException \Chapi\Exception\DatePeriodException
     */
    public function testParseIso8601StringFailure()
    {
        $datePeriodFactory = new DatePeriodFactory();

        $this->assertNull(
            $datePeriodFactory->createIso8601Entity('2015-07-07T01:00:00Z/P1D')
        );
    }

    public function testCreateDatePeriodSuccess()
    {
        $datePeriodFactory = new DatePeriodFactory();
        $testDate = date('Y-m-d');

        /** @var \DatePeriod $datePeriod */
        $datePeriod = $datePeriodFactory->createDatePeriod('R/' . $testDate . 'T01:00:00Z/P1D');

        foreach ($datePeriod as $dateTime) {
            $datesA[] = $dateTime->format("Y-m-dH:i");
        }

        $this->assertEquals(
            3,
            count($datesA)
        );

        $this->assertEquals(
            date('YmdHi', strtotime($testDate  . '01:00 -1day')),
            date('YmdHi', strtotime($datesA[0]))
        );

        $this->assertEquals(
            date('YmdHi', strtotime($testDate  . '01:00')),
            date('YmdHi', strtotime($datesA[1]))
        );

        $this->assertEquals(
            date('YmdHi', strtotime($testDate  . '01:00 +1day')),
            date('YmdHi', strtotime($datesA[2]))
        );
    }

    /**
     * @expectedException \Chapi\Exception\DatePeriodException
     */
    public function testCreateDatePeriodFailure()
    {
        $datePeriodFactory = new DatePeriodFactory();

        $this->assertNull(
            $datePeriodFactory->createDatePeriod('2015-07-07T01:00:00Z/P1D')
        );
    }

    public function testCreateDatePeriodWithSummerWinterTimezoneTime()
    {
        $orgTimeZone = ini_get('date.timezone');

        $datePeriodFactory = new DatePeriodFactory();
        $testYear = date('Y', strtotime('-1year'));

        ini_set('date.timezone', 'Europe/Berlin');

        /** @var \DatePeriod $datePeriod */
        $datePeriod = $datePeriodFactory->createDatePeriod('R/' . $testYear . '-01-30T23:00:00Z/P1M', 'UTC'); // winter time berlin (GMT+1)
        foreach ($datePeriod as $dateTime) {
            $dateA = $dateTime;
        }

        /** @var \DatePeriod $_oDatePeriod */
        $datePeriod = $datePeriodFactory->createDatePeriod('R/' . $testYear . '-04-30T23:00:00Z/P1M', 'UTC'); // summer time berlin (GMT+2)
        foreach ($datePeriod as $dateTime) {
            $dateB = $dateTime;
        }

        $this->assertEquals(
            $dateA->format('Hi'),
            $dateB->format('Hi')
        );

        // restore default timezone
        ini_set('date.timezone', $orgTimeZone);
    }
}
