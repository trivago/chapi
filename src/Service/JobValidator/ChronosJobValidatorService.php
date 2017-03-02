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
     * ChronosJobValidatorService constructor.
     * @param ValidatorFactoryInterface $oValidatorFactory
     */
    public function __construct(
        ValidatorFactoryInterface $oValidatorFactory
    )
    {
        $this->oValidatorFactory = $oValidatorFactory;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function isEntityValid(JobEntityInterface $oJobEntity)
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
     * @param JobEntityInterface $oJobEntity
     * @return array
     */
    public function getInvalidProperties(JobEntityInterface $oJobEntity)
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
     * @param JobEntityInterface $oJobEntity
     * @return array
     */
    private function validateJobEntity(JobEntityInterface $oJobEntity)
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
     * @param JobEntityInterface $oJobEntity
     * @return ValidationResult
     */
    private function getValidationResult($iValidator, $sProperty, JobEntityInterface $oJobEntity)
    {
        $_oValidator = $this->oValidatorFactory->getValidator($iValidator);
        return new ValidationResult(
            $sProperty,
            $_oValidator->isValid($sProperty, $oJobEntity),
            $_oValidator->getLastErrorMessage()
        );
    }
}