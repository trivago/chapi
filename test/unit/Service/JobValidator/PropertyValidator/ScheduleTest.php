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
    private $datePeriodFactory;

    use JobEntityTrait;

    public function setUp()
    {
        $this->datePeriodFactory = $this->prophesize('Chapi\Component\DatePeriod\DatePeriodFactoryInterface');
    }

    public function testIsValidSuccess()
    {
        $this->datePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(
                new \DatePeriod(
                    new \DateTime('-1 day'),
                    new \DateInterval('PT10M'),
                    new \DateTime('+ 1 day')
                )
            )
        ;
        
        $propertyValidator = new Schedule($this->datePeriodFactory->reveal());

        $this->handleValidTestCase($propertyValidator, 'schedule', 'foo');
        $this->handleValidTestCase($propertyValidator, 'parents', []);

        $jobEntity = $this->getValidDependencyJobEntity();
        $this->handleValidTestCase($propertyValidator, 'schedule', '', $jobEntity);
        $this->handleValidTestCase($propertyValidator, 'parents', ['foo', 'bar'], $jobEntity);
    }

    public function testIsValidFailure()
    {
        $this->datePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(false)
        ;

        $propertyValidator = new Schedule($this->datePeriodFactory->reveal());

        $this->handleInvalidTestCase($propertyValidator, 'schedule', '');

        $jobEntity = $this->getValidDependencyJobEntity();
        $this->handleInvalidTestCase($propertyValidator, 'parents', [], $jobEntity);
        
        // test with createDatePeriod return value
        $this->handleInvalidTestCase($propertyValidator, 'schedule', 'foo');
        
        // Spies
        $this->datePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->shouldHaveBeenCalledTimes(1)
        ;
    }

    public function testIsValidFailureWithException()
    {
        $this->datePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willThrow(
                new DatePeriodException('test exception')
            )
        ;

        $propertyValidator = new Schedule($this->datePeriodFactory->reveal());
        $this->handleInvalidTestCase($propertyValidator, 'schedule', 'notSupportedString');
    }

    public function testIsValidFailureForDependencyJobWithScheduling()
    {
        $propertyValidator = new Schedule($this->datePeriodFactory->reveal());
        
        // test !empty schedule property
        $jobEntity = $this->getValidDependencyJobEntity();
        $jobEntity->schedule = 'R/2015-09-01T02:00:00Z/P1M';
        $this->handleInvalidTestCase($propertyValidator, 'jobname', 'foo', $jobEntity);
    }

    public function testGetLastErrorMessageMulti()
    {
        $this->datePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(
                new \DatePeriod(
                    new \DateTime('-1 day'),
                    new \DateInterval('PT10M'),
                    new \DateTime('+ 1 day')
                )
            )
        ;
        
        $propertyValidator = new Schedule($this->datePeriodFactory->reveal());
        
        $this->handleErrorMessageMultiTestCase($propertyValidator, 'schedule', 'foo', '');
    }
}
