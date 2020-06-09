<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-11
 *
 */

namespace unit\Service\JobValidator\PropertyValidator;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;
use ChapiTest\src\TestTraits\JobEntityTrait;

abstract class AbstractValidatorTest extends \PHPUnit\Framework\TestCase
{
    use JobEntityTrait;

    /**
     * @param PropertyValidatorInterface $validator
     * @param string $property
     * @param mixed $validValue
     * @param mixed $invalidValue
     * @param JobEntityInterface|null $jobEntity
     */
    protected function handleErrorMessageMultiTestCase(
        PropertyValidatorInterface $validator,
        $property,
        $validValue,
        $invalidValue,
        JobEntityInterface $jobEntity = null
    ) {
        if (is_null($jobEntity)) {
            $jobEntity = $this->getValidScheduledJobEntity();
        }

        $jobEntity->{$property} = $invalidValue;
        $this->assertFalse($validator->isValid($property, $jobEntity));
        $this->assertStringContainsString($property, $validator->getLastErrorMessage());

        $jobEntity->{$property} = $validValue;
        $this->assertTrue($validator->isValid($property, $jobEntity));
        $this->assertEmpty($validator->getLastErrorMessage());
    }

    /**
     * @param PropertyValidatorInterface $validator
     * @param string $property
     * @param mixed $validValue
     * @param JobEntityInterface|null $jobEntity
     */
    protected function handleValidTestCase(
        PropertyValidatorInterface $validator,
        $property,
        $validValue,
        JobEntityInterface $jobEntity = null
    ) {
        if (is_null($jobEntity)) {
            $jobEntity = $this->getValidScheduledJobEntity();
        }

        $jobEntity->{$property} = $validValue;
        $this->assertTrue($validator->isValid($property, $jobEntity));
        $this->assertEmpty($validator->getLastErrorMessage());
    }

    /**
     * @param PropertyValidatorInterface $validator
     * @param $property
     * @param $invalidValue
     * @param JobEntityInterface|null $jobEntity
     */
    protected function handleInvalidTestCase(
        PropertyValidatorInterface $validator,
        $property,
        $invalidValue,
        JobEntityInterface $jobEntity = null
    ) {
        if (is_null($jobEntity)) {
            $jobEntity = $this->getValidScheduledJobEntity();
        }

        $jobEntity->{$property} = $invalidValue;
        $this->assertFalse($validator->isValid($property, $jobEntity));
        $this->assertStringContainsString($property, $validator->getLastErrorMessage());
    }
}
