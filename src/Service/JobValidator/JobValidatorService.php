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
use Chapi\Entity\JobValidator\ValidationResult;

class JobValidatorService implements JobValidatorServiceInterface
{
    /**
     * @var ValidatorFactoryInterface
     */
    private $oValidatorFactory;

    /**
     * @var array
     */
    private static $aValidationMap = [
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
     * JobValidatorService constructor.
     * @param ValidatorFactoryInterface $oValidatorFactory
     */
    public function __construct(
        ValidatorFactoryInterface $oValidatorFactory
    )
    {
        $this->oValidatorFactory = $oValidatorFactory;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntity $oJobEntity)
    {
        foreach ($this->validateJobEntity($oJobEntity) as $_oValidatorResult)
        {
            if (!$_oValidatorResult->bIsValid)
            {
                return false;
            }
        }

        return true;
    }
    
    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntity $oJobEntity)
    {
        $_aValidationFields = $this->validateJobEntity($oJobEntity);

        $_aInvalidFields = [];
        foreach ($_aValidationFields as $_sProperty => $_oValidationResult)
        {
            if (false == $_oValidationResult->bIsValid)
            {
                $_aInvalidFields[$_sProperty] = $_oValidationResult->sErrorMessage;
            }
        }

        return $_aInvalidFields;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return array
     */
    private function validateJobEntity(JobEntity $oJobEntity)
    {
        $_aValidProperties = [];

        foreach (self::$aValidationMap as $_sProperty => $_iValidator)
        {
            $_aValidProperties[$_sProperty] = $this->getValidationResult($_iValidator, $_sProperty, $oJobEntity);
        }

        return $_aValidProperties;
    }

    /**
     * @param int $iValidator
     * @param string $sProperty
     * @param JobEntity $oJobEntity
     * @return ValidationResult
     */
    private function getValidationResult($iValidator, $sProperty, JobEntity $oJobEntity)
    {
        $_oValidator = $this->oValidatorFactory->getValidator($iValidator);
        return new ValidationResult(
            $sProperty,
            $_oValidator->isValid($sProperty, $oJobEntity),
            $_oValidator->getLastErrorMessage()
        );
    }
}