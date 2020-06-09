<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-14
 *
 */


namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Entity\DatePeriod\Iso8601Entity;
use Chapi\Service\JobValidator\PropertyValidator\Epsilon;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class EpsilonTest extends AbstractValidatorTest
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $datePeriodFactory;

    use JobEntityTrait;

    protected function setUp(): void
    {
        $this->datePeriodFactory = $this->prophesize('Chapi\Component\DatePeriod\DatePeriodFactoryInterface');
    }

    public function testIsValidSuccess()
    {
        $jobEntity= $this->setUpEntityByInterval('PT30M');
        $propertyValidator = new Epsilon($this->datePeriodFactory->reveal());

        $this->handleValidTestCase($propertyValidator, 'epsilon', 'PT5M', $jobEntity);
        $this->handleValidTestCase($propertyValidator, 'epsilon', 'PT15M', $jobEntity);

        // additional test case
        $jobEntity= $this->setUpEntityByInterval('PT30S');
        $propertyValidator = new Epsilon($this->datePeriodFactory->reveal());

        $this->handleValidTestCase($propertyValidator, 'epsilon', 'PT30S', $jobEntity);

        // spies
        $this->datePeriodFactory
            ->createIso8601Entity(Argument::exact($jobEntity->schedule))
            ->shouldNotHaveBeenCalled()
        ;
    }

    public function testIsValidFailure()
    {
        $jobEntity= $this->setUpEntityByInterval('PT15M');
        $propertyValidator = new Epsilon($this->datePeriodFactory->reveal());

        $this->handleInvalidTestCase($propertyValidator, 'epsilon', 'PT15M', $jobEntity);
        $this->handleInvalidTestCase($propertyValidator, 'epsilon', 'PT60M', $jobEntity);
        $this->handleInvalidTestCase($propertyValidator, 'epsilon', null, $jobEntity);
        $this->handleInvalidTestCase($propertyValidator, 'epsilon', 'foo', $jobEntity);
        $this->handleInvalidTestCase($propertyValidator, 'epsilon', 30, $jobEntity);
    }

    public function testGetLastErrorMessageMulti()
    {
        $jobEntity= $this->setUpEntityByInterval('PT1H');
        $propertyValidator = new Epsilon($this->datePeriodFactory->reveal());

        $this->handleErrorMessageMultiTestCase($propertyValidator, 'epsilon', 'PT15M', 'PT120M', $jobEntity);
    }

    private function setUpEntityByInterval($interval)
    {
        $jobEntity = $this->getValidScheduledJobEntity();
        $jobEntity->schedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/' . $interval;

        $iso8601Entity = new Iso8601Entity($jobEntity->schedule);

        $this->datePeriodFactory
            ->createIso8601Entity(Argument::exact($jobEntity->schedule))
            ->willReturn($iso8601Entity)
        ;

        return $jobEntity;
    }
}
