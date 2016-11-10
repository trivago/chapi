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
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->willReturn(true);
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
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->shouldHaveBeenCalled();
    }

    public function testIsEntityValidFailure()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->willReturn(false);
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
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->shouldHaveBeenCalled();
        $this->oPropertyValidator->getLastErrorMessage()->shouldHaveBeenCalled();
    }
    
    public function testGetInvalidPropertiesSuccess()
    {
        // mock
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->willReturn(true);
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
        $this->oPropertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\JobEntity'))->willReturn(false);
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

    /*public function testIsEntityValidScheduledFailure()
    {
        $_oIso8601Entity = new Iso8601Entity('R/2015-09-01T02:00:00Z/P1M');

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::type('string'))
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // -------------------------------------
        // test empty JobEntity
        // -------------------------------------
        $_oJobEntity = new JobEntity();
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // -------------------------------------
        // test empty properties
        // -------------------------------------
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->name = '';

        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->command = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->description = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->owner = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->ownerName = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->epsilon = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // -------------------------------------
        // test not boolean properties
        // -------------------------------------
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->async = 'false';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->disabled = 'false';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->softError = 'false';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->highPriority = 'false';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->parents = ['JobB'];
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // -------------------------------------
        // test numeric properties
        // -------------------------------------
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->retries = -1;
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // -------------------------------------
        // new instance to test schedule failing with exception
        // -------------------------------------
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willThrow(
                new DatePeriodException('test exception')
            )
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->schedule = 'notSupportedString';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // -------------------------------------
        // new instance to test schedule failing
        // -------------------------------------
        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->willReturn(false)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->schedule = 'notSupportedString';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityValidDependencySuccess()
    {
        $_oJobEntity = $this->getValidDependencyJobEntity();

        $this->oDatePeriodFactory
            ->createDatePeriod(Argument::type('string'), Argument::type('string'))
            ->shouldNotBeCalled()
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityValidDependencyFailure()
    {
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // -------------------------------------
        // test !empty schedule property
        // -------------------------------------
        $_oJobEntity = $this->getValidDependencyJobEntity();
        $_oJobEntity->schedule = 'R/2015-09-01T02:00:00Z/P1M';

        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityValidWithInvalidNameFailure()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oIso8601Entity = new Iso8601Entity($_oJobEntity->schedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldBeCalledTimes(1)
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // -------------------------------------
        // test invalid job name
        // -------------------------------------
        $_oJobEntity->name = 'JobA:do_something';

        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityInvalidWithEqualsEpsilonAndScheduling()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oIso8601Entity = new Iso8601Entity($_oJobEntity->schedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldBeCalledTimes(1)
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // test
        $_oJobEntity->epsilon = $_oIso8601Entity->sInterval;

        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityInvalidWithGraterEpsilonThanScheduling()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oIso8601Entity = new Iso8601Entity($_oJobEntity->schedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldBeCalledTimes(1)
            ->willReturn($_oIso8601Entity)
        ;


        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        //test
        $_oJobEntity->epsilon = 'P1D';

        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityValidWithEpsilon30S()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->schedule = 'R/2015-10-01T02:00:00Z/PT30S';
        $_oJobEntity->epsilon = 'PT30S';

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldNotBeCalled()
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        //test
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testIsEntityInvalidWithWrongEpsilon()
    {
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->epsilon = '30';

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::exact($_oJobEntity->schedule))
            ->shouldNotBeCalled()
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // test
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testValidationForConstraints()
    {
        // setup
        $_sSchedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/PT30M';
        $_oIso8601Entity = new Iso8601Entity($_sSchedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::type('string'))
            ->willReturn($_oIso8601Entity)
        ;
        
        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );
        
        // invalid
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->constraints[] = ['a', 'like'];
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
        
        
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->constraints[] = ['a', 'like', 'b'];
        $_oJobEntity->constraints[] = ['c', 'like'];
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
        
        
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->constraints[] = 'foo';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
        
        
        // valid
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->constraints[] = ['a', 'like', 'b'];
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        
        $_oJobEntity = $this->getValidScheduledJobEntity();
        $_oJobEntity->constraints[] = ['a', 'like', 'b'];
        $_oJobEntity->constraints[] = ['c', 'like', 'd'];
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testValidationForContainer()
    {
        // setup
        $_sSchedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/PT30M';
        $_oIso8601Entity = new Iso8601Entity($_sSchedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::type('string'))
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // invalid
        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container = 'foo';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );


        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->type = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->image = '';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        // valid
        $_oJobEntity = $this->getValidContainerJobEntity();
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
        
        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->network = null;
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testValidationForContainerVolumes()
    {
        // setup
        $_sSchedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/PT30M';
        $_oIso8601Entity = new Iso8601Entity($_sSchedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::type('string'))
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );

        // invalid
        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->volumes[0]->mode = 'R';
        $this->assertFalse(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );


        // valid
        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->volumes[0]->mode = 'RO';
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );

        $_oJobEntity = $this->getValidContainerJobEntity();
        $_oJobEntity->container->volumes[0]->mode = 'RW';
        $this->assertTrue(
            $_oJobEntityValidatorService->isEntityValid($_oJobEntity)
        );
    }

    public function testGetInvalidProperties()
    {
        // setup
        $_sSchedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/PT30M';
        $_oIso8601Entity = new Iso8601Entity($_sSchedule);

        $this->oDatePeriodFactory
            ->createIso8601Entity(Argument::type('string'))
            ->willReturn($_oIso8601Entity)
        ;

        $_oJobEntityValidatorService = new JobValidatorService(
            $this->oDatePeriodFactory->reveal(),
            $this->oValidatorFactory->reveal()
        );
        
        $_oJobEntity = $this->getValidScheduledJobEntity('JobA');
        $_oJobEntity->container = 'foo';
        
        $this->assertTrue(in_array('container', $_oJobEntityValidatorService->getInvalidProperties($_oJobEntity)));
    }
    */
}