<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;


use Chapi\Exception\DatePeriodException;
use Chapi\Service\JobValidator\PropertyValidator\Schedule;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class ScheduleTest extends AbstractValidatorTest
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oDatePeriodFactory;

    use JobEntityTrait;

    public function setUp()
    {
        $this->oDatePeriodFactory = $this->prophesize('Chapi\Component\DatePeriod\DatePeriodFactoryInterface');
    }

    public function testIsValidSuccess()
    {
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(
                new \DatePeriod(
                    new \DateTime('-1 day'),
                    new \DateInterval('PT10M'),
                    new \DateTime('+ 1 day')
                )
            )
        ;
        
        $_oPropertyValidator = new Schedule($this->oDatePeriodFactory->reveal());

        $this->handleValidTestCase($_oPropertyValidator, 'schedule', 'foo');
        $this->handleValidTestCase($_oPropertyValidator, 'parents', []);

        $_oJobEntity = $this->getValidDependencyJobEntity();
        $this->handleValidTestCase($_oPropertyValidator, 'schedule', '', $_oJobEntity);
        $this->handleValidTestCase($_oPropertyValidator, 'parents', ['foo', 'bar'], $_oJobEntity);
    }

    public function testIsValidFailure()
    {
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(false)
        ;

        $_oPropertyValidator = new Schedule($this->oDatePeriodFactory->reveal());

        $this->handleInvalidTestCase($_oPropertyValidator, 'schedule', '');

        $_oJobEntity = $this->getValidDependencyJobEntity();
        $this->handleInvalidTestCase($_oPropertyValidator, 'parents', [], $_oJobEntity);
        
        // test with createDatePeriod return value
        $this->handleInvalidTestCase($_oPropertyValidator, 'schedule', 'foo');
        
        // Spies
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->shouldHaveBeenCalledTimes(1)
        ;
    }

    public function testIsValidFailureWithException()
    {
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willThrow(
                new DatePeriodException('test exception')
            )
        ;

        $_oPropertyValidator = new Schedule($this->oDatePeriodFactory->reveal());
        $this->handleInvalidTestCase($_oPropertyValidator, 'schedule', 'notSupportedString');
    }

    public function testIsValidFailureForDependencyJobWithScheduling()
    {
        $_oPropertyValidator = new Schedule($this->oDatePeriodFactory->reveal());
        
        // test !empty schedule property
        $_oJobEntity = $this->getValidDependencyJobEntity();
        $_oJobEntity->schedule = 'R/2015-09-01T02:00:00Z/P1M';
        $this->handleInvalidTestCase($_oPropertyValidator, 'jobname', 'foo', $_oJobEntity);
    }

    public function testGetLastErrorMessageMulti()
    {
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(
                new \DatePeriod(
                    new \DateTime('-1 day'),
                    new \DateInterval('PT10M'),
                    new \DateTime('+ 1 day')
                )
            )
        ;
        
        $_oPropertyValidator = new Schedule($this->oDatePeriodFactory->reveal());
        
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'schedule', 'foo', '');
    }
}