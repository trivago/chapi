<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */

namespace Chapi\Service\JobValidator;

use Chapi\Component\DatePeriod\DatePeriodFactoryInterface;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Entity\JobValidator\ValidationResult;

class ChronosJobValidatorService implements JobValidatorServiceInterface
{
    /**
     * @var ValidatorFactoryInterface
     */
    private $validatorFactory;

    /**
     * @var array
     */
    private static $validationMap = [
        'name' => ValidatorFactoryInterface::NAME_VALIDATOR,
        'command' => ValidatorFactoryInterface::COMMAND_VALIDATOR,
        'description' => ValidatorFactoryInterface::NOT_EMPTY_VALIDATOR,
        'owner' => ValidatorFactoryInterface::NOT_EMPTY_VALIDATOR,
        'ownerName' => ValidatorFactoryInterface::NOT_EMPTY_VALIDATOR,
        'epsilon' => ValidatorFactoryInterface::EPSILON_VALIDATOR,
        'async' => ValidatorFactoryInterface::BOOLEAN_VALIDATOR,
        'disabled' => ValidatorFactoryInterface::BOOLEAN_VALIDATOR,
        'softError' => ValidatorFactoryInterface::BOOLEAN_VALIDATOR,
        'highPriority' => ValidatorFactoryInterface::BOOLEAN_VALIDATOR,
        'schedule' => ValidatorFactoryInterface::SCHEDULE_VALIDATOR,
        'parents' => ValidatorFactoryInterface::ARRAY_VALIDATOR,
        'retries' => ValidatorFactoryInterface::RETRY_VALIDATOR,
        'constraints' => ValidatorFactoryInterface::CONSTRAINTS_VALIDATOR,
        'container' => ValidatorFactoryInterface::CONTAINER_VALIDATOR,
    ];

    /**
     * ChronosJobValidatorService constructor.
     * @param ValidatorFactoryInterface $validatorFactory
     */
    public function __construct(
        ValidatorFactoryInterface $validatorFactory
    ) {
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function isEntityValid(JobEntityInterface $jobEntity)
    {
        foreach ($this->validateJobEntity($jobEntity) as $validatorResult) {
            if (!$validatorResult->isValid) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntityInterface $jobEntity)
    {
        $validationFields = $this->validateJobEntity($jobEntity);

        $invalidFields = [];
        foreach ($validationFields as $property => $validationResult) {
            if (false == $validationResult->isValid) {
                $invalidFields[$property] = $validationResult->errorMessage;
            }
        }

        return $invalidFields;
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return array
     */
    private function validateJobEntity(JobEntityInterface $jobEntity)
    {
        $validProperties = [];

        foreach (self::$validationMap as $property => $validator) {
            $validProperties[$property] = $this->getValidationResult($validator, $property, $jobEntity);
        }

        return $validProperties;
    }

    /**
     * @param int $validator
     * @param string $property
     * @param JobEntityInterface $oJobEntityjobEntity
     * @return ValidationResult
     */
    private function getValidationResult($validator, $property, JobEntityInterface $oJobEntityjobEntity)
    {
        $validator = $this->validatorFactory->getValidator($validator);
        return new ValidationResult(
            $property,
            $validator->isValid($property, $oJobEntityjobEntity),
            $validator->getLastErrorMessage()
        );
    }
}
