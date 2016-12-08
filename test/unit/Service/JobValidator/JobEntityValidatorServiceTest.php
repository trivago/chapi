<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-11
 *
 */

namespace ChapiTest\unit\Service\JobValidator;


use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobValidator\JobValidatorService;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class JobEntityValidatorServiceTest extends \PHPUnit_Framework_TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oPropertyValidator;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $oValidatorFactory;

    public function setUp()
    {
        $this->oPropertyValidator = $this->prophesize('Chapi\Service\JobValidator\PropertyValidatorInterface');
        $this->oValidatorFactory = $this->prophesize('Chapi\Service\JobValidator\ValidatorFactoryInterface');
    }
    
    public function testIsEntityValidSuccess()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(true);
        $this->oPropertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->oValidatorFactory->getValidator(Argument::type('int'))->willReturn($this->oPropertyValidator->reveal());

        // setup
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oValidatorFactory->reveal()
        );

        // test
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // Spies
        $this->oValidatorFactory->getValidator(Argument::type('int'))->shouldHaveBeenCalled();
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->shouldHaveBeenCalled();
    }

    public function testIsEntityValidFailure()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(false);
        $this->oPropertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->oValidatorFactory->getValidator(Argument::type('int'))->willReturn($this->oPropertyValidator->reveal());

        // setup
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oValidatorFactory->reveal()
        );

        // test
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // Spies
        $this->oValidatorFactory->getValidator(Argument::type('int'))->shouldHaveBeenCalled();
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->shouldHaveBeenCalled();
        $this->oPropertyValidator->getLastErrorMessage()->shouldHaveBeenCalled();
    }
    
    public function testGetInvalidPropertiesSuccess()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(true);
        $this->oPropertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->oValidatorFactory->getValidator(Argument::type('int'))->willReturn($this->oPropertyValidator->reveal());

        // setup
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oValidatorFactory->reveal()
        );
        
        // test
        $this->assertEquals(
            0,
            count($_oJobEntityValidatorService->getInvalidProperties($_oJobEntity))
        );
    }

    public function testGetInvalidPropertiesFailure()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(false);
        $this->oPropertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->oValidatorFactory->getValidator(Argument::type('int'))->willReturn($this->oPropertyValidator->reveal());

        // setup
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oValidatorFactory->reveal()
        );

        // test
        $_aResult = $_oJobEntityValidatorService->getInvalidProperties($_oJobEntity);
        
        $this->assertGreaterThan(
            0,
            count($_aResult)
        );
        
        foreach ($_aResult as $_sErrMsg)
        {
            $this->assertEquals('error message', $_sErrMsg);   
            break; // one check is enough
        }
    }
}