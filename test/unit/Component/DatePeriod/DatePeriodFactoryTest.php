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

        $_aExpectedMatchValues = [
            'R/2015-07-07T01:00:00Z/P1D',
            'R',
            '2015-07-07T01:00:00Z',
            'P1D'
        ];
        $this->assertEquals(
            $_aExpectedMatchValues,
            $_oDatePeriodFactory->parseIso8601String($_aExpectedMatchValues[0])
        );

        $_aExpectedMatchValues = [
            'R0/2015-07-07T01:00:00Z/PT1M',
            'R0',
            '2015-07-07T01:00:00Z',
            'PT1M'
        ];
        $this->assertEquals(
            $_aExpectedMatchValues,
            $_oDatePeriodFactory->parseIso8601String($_aExpectedMatchValues[0])
        );
    }

    /**
     * @expectedException \Chapi\Exception\DatePeriodException
     */
    public function testParseIso8601StringFailure()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();

        $this->assertNull(
            $_oDatePeriodFactory->parseIso8601String('2015-07-07T01:00:00Z/P1D')
        );
    }

    public function testCreateDatePeriodSuccess()
    {
        $_oDatePeriodFactory = new DatePeriodFactory();
        $_sTestDate = date('Y-m-d');

        /** @var \DatePeriod $_oDatePeriod */
        $_oDatePeriod = $_oDatePeriodFactory->createDatePeriod('R/' . $_sTestDate . 'T01:00:00Z/P1D', 'Europe/Berlin');

        foreach($_oDatePeriod as $_oDateTime)
        {
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
}