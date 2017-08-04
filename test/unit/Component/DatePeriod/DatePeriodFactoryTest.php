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

class DatePeriodFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testParseIso8601StringSuccess()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();

        $_oIso8601Entity = $_oDatePeriodFactory->createIso8601Entity('R/2015-07-07T01:00:00Z/P1D');

        $this->assertEquals('R/2015-07-07T01:00:00Z/P1D', $_oIso8601Entity->iso8601);
        $this->assertEquals('R', $_oIso8601Entity->repeat);
        $this->assertEquals('2015-07-07T01:00:00Z', $_oIso8601Entity->startTime);
        $this->assertEquals('P1D', $_oIso8601Entity->interval);


        $_oIso8601Entity = $_oDatePeriodFactory->createIso8601Entity('R0/2015-07-07T01:00:00Z/PT1M');

        $this->assertEquals('R0/2015-07-07T01:00:00Z/PT1M', $_oIso8601Entity->iso8601);
        $this->assertEquals('R0', $_oIso8601Entity->repeat);
        $this->assertEquals('2015-07-07T01:00:00Z', $_oIso8601Entity->startTime);
        $this->assertEquals('PT1M', $_oIso8601Entity->interval);
    }

    /**
     * @expectedException \Chapi\Exception\DatePeriodException
     */
    public function testParseIso8601StringFailure()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();

        $this->assertNull(
            $_oDatePeriodFactory->createIso8601Entity('2015-07-07T01:00:00Z/P1D')
        );
    }

    public function testCreateDatePeriodSuccess()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();
        $_sTestDate = date('Y-m-d');

        /** @var \DatePeriod $_oDatePeriod */
        $_oDatePeriod = $_oDatePeriodFactory->createDatePeriod('R/' . $_sTestDate . 'T01:00:00Z/P1D');

        foreach ($_oDatePeriod as $_oDateTime) {
            $_aDatesA[] = $_oDateTime->format("Y-m-dH:i");
        }

        $this->assertEquals(
            3,
            count($_aDatesA)
        );

        $this->assertEquals(
            date('YmdHi', strtotime($_sTestDate  . '01:00 -1day')),
            date('YmdHi', strtotime($_aDatesA[0]))
        );

        $this->assertEquals(
            date('YmdHi', strtotime($_sTestDate  . '01:00')),
            date('YmdHi', strtotime($_aDatesA[1]))
        );

        $this->assertEquals(
            date('YmdHi', strtotime($_sTestDate  . '01:00 +1day')),
            date('YmdHi', strtotime($_aDatesA[2]))
        );
    }

    /**
     * @expectedException \Chapi\Exception\DatePeriodException
     */
    public function testCreateDatePeriodFailure()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();

        $this->assertNull(
            $_oDatePeriodFactory->createDatePeriod('2015-07-07T01:00:00Z/P1D')
        );
    }

    public function testCreateDatePeriodWithSummerWinterTimezoneTime()
    {
        $_sOrgTimeZone = ini_get('date.timezone');

        $_oDatePeriodFactory = new DatePeriodFactory();
        $_sTestYear = date('Y', strtotime('-1year'));

        ini_set('date.timezone', 'Europe/Berlin');

        /** @var \DatePeriod $_oDatePeriod */
        $_oDatePeriod = $_oDatePeriodFactory->createDatePeriod('R/' . $_sTestYear . '-01-30T23:00:00Z/P1M', 'UTC'); // winter time berlin (GMT+1)
        foreach ($_oDatePeriod as $_oDateTime) {
            $_oDateA = $_oDateTime;
        }

        /** @var \DatePeriod $_oDatePeriod */
        $_oDatePeriod = $_oDatePeriodFactory->createDatePeriod('R/' . $_sTestYear . '-04-30T23:00:00Z/P1M', 'UTC'); // summer time berlin (GMT+2)
        foreach ($_oDatePeriod as $_oDateTime) {
            $_oDateB = $_oDateTime;
        }

        $this->assertEquals(
            $_oDateA->format('Hi'),
            $_oDateB->format('Hi')
        );

        // restore default timezone
        ini_set('date.timezone', $_sOrgTimeZone);
    }
}
