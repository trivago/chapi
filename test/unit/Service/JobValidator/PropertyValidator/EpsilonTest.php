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
    private $oDatePeriodFactory;

    use JobEntityTrait;

    public function setUp()
    {
        $this->oDatePeriodFactory = $this->prophesize('Chapi\Component\DatePeriod\DatePeriodFactoryInterface');
    }
    
    public function testIsValidSuccess()
    {
        $_oJobEntity= $this->setUpEntityByInterval('PT30M');
        $_oPropertyValidator = new Epsilon($this->oDatePeriodFactory->reveal());
        
        $this->handleValidTestCase($_oPropertyValidator, 'epsilon', 'PT5M', $_oJobEntity);
        $this->handleValidTestCase($_oPropertyValidator, 'epsilon', 'PT15M', $_oJobEntity);

        // additional test case
        $_oJobEntity= $this->setUpEntityByInterval('PT30S');
        $_oPropertyValidator = new Epsilon($this->oDatePeriodFactory->reveal());

        $this->handleValidTestCase($_oPropertyValidator, 'epsilon', 'PT30S', $_oJobEntity);

        // spies
        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldNotHaveBeenCalled()
        ;
    }

    public function testIsValidFailure()
    {
        $_oJobEntity= $this->setUpEntityByInterval('PT15M');
        $_oPropertyValidator = new Epsilon($this->oDatePeriodFactory->reveal());

        $this->handleInvalidTestCase($_oPropertyValidator, 'epsilon', 'PT15M', $_oJobEntity);
        $this->handleInvalidTestCase($_oPropertyValidator, 'epsilon', 'PT60M', $_oJobEntity);
        $this->handleInvalidTestCase($_oPropertyValidator, 'epsilon', null, $_oJobEntity);
        $this->handleInvalidTestCase($_oPropertyValidator, 'epsilon', 'foo', $_oJobEntity);
        $this->handleInvalidTestCase($_oPropertyValidator, 'epsilon', 30, $_oJobEntity);
    }

    public function testGetLastErrorMessageMulti()
    {
        $_oJobEntity= $this->setUpEntityByInterval('PT1H');
        $_oPropertyValidator = new Epsilon($this->oDatePeriodFactory->reveal());
        
        $this->handleErrorMessageMultiTestCase($_oPropertyValidator, 'epsilon', 'PT15M', 'PT120M', $_oJobEntity);
    }

    private function setUpEntityByInterval($sInterval)
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->schedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/' . $sInterval;

        $_oIso8601Entity = new Iso8601Entity($_oJobEntity->schedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->willReturn($_oIso8601Entity)
        ;

        return $_oJobEntity;
    }
}