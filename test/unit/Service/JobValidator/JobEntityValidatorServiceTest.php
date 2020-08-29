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
use Chapi\Service\JobValidator\ChronosJobValidatorService;
use ChapiTest\src\TestTraits\JobEntityTrait;
use Prophecy\Argument;

class JobEntityValidatorServiceTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $propertyValidator;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $validatorFactory;

    protected function setUp(): void
    {
        $this->propertyValidator = $this->prophesize('Chapi\Service\JobValidator\PropertyValidatorInterface');
        $this->validatorFactory = $this->prophesize('Chapi\Service\JobValidator\ValidatorFactoryInterface');
    }

    public function testIsEntityValidSuccess()
    {
        // mock
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(true);
        $this->propertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->validatorFactory->getValidator(Argument::type('int'))->willReturn($this->propertyValidator->reveal());

        // setup
        $jobEntity = $this->getValidScheduledJobEntity();
        $jobEntityValidatorService = new ChronosJobValidatorService(
            $this->validatorFactory->reveal()
        );

        // test
        $this->assertTrue(
            $jobEntityValidatorService->isEntityValid($jobEntity)
        );

        // Spies
        $this->validatorFactory->getValidator(Argument::type('int'))->shouldHaveBeenCalled();
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->shouldHaveBeenCalled();
    }

    public function testIsEntityValidFailure()
    {
        // mock
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(false);
        $this->propertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->validatorFactory->getValidator(Argument::type('int'))->willReturn($this->propertyValidator->reveal());

        // setup
        $jobEntity = $this->getValidScheduledJobEntity();
        $jobEntityValidatorService = new ChronosJobValidatorService(
            $this->validatorFactory->reveal()
        );

        // test
        $this->assertFalse(
            $jobEntityValidatorService->isEntityValid($jobEntity)
        );

        // Spies
        $this->validatorFactory->getValidator(Argument::type('int'))->shouldHaveBeenCalled();
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->shouldHaveBeenCalled();
        $this->propertyValidator->getLastErrorMessage()->shouldHaveBeenCalled();
    }

    public function testGetInvalidPropertiesSuccess()
    {
        // mock
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(true);
        $this->propertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->validatorFactory->getValidator(Argument::type('int'))->willReturn($this->propertyValidator->reveal());

        // setup
        $jobEntity = $this->getValidScheduledJobEntity();
        $jobEntityValidatorService = new ChronosJobValidatorService(
            $this->validatorFactory->reveal()
        );

        // test
        $this->assertCount(
            0,
            $jobEntityValidatorService->getInvalidProperties($jobEntity)
        );
    }

    public function testGetInvalidPropertiesFailure()
    {
        // mock
        $this->propertyValidator->isValid(Argument::type('string'), Argument::type('Chapi\Entity\Chronos\ChronosJobEntity'))->willReturn(false);
        $this->propertyValidator->getLastErrorMessage()->willReturn('error message');

        $this->validatorFactory->getValidator(Argument::type('int'))->willReturn($this->propertyValidator->reveal());

        // setup
        $jobEntity = $this->getValidScheduledJobEntity();
        $jobEntityValidatorService = new ChronosJobValidatorService(
            $this->validatorFactory->reveal()
        );

        // test
        $result = $jobEntityValidatorService->getInvalidProperties($jobEntity);

        $this->assertGreaterThan(
            0,
            count($result)
        );

        foreach ($result as $errorMessage) {
            $this->assertSame('error message', $errorMessage);
            break; // one check is enough
        }
    }
}
