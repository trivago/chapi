<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */

namespace Chapi\Service\JobValidator;


use Chapi\Entity\Chronos\JobEntity;
use Chapi\Service\JobValidator\PropertyValidator\Command;
use Chapi\Service\JobValidator\PropertyValidator\Constraints;
use Chapi\Service\JobValidator\PropertyValidator\Container;
use Chapi\Service\JobValidator\PropertyValidator\Epsilon;
use Chapi\Service\JobValidator\PropertyValidator\IsArray;
use Chapi\Service\JobValidator\PropertyValidator\IsBoolean;
use Chapi\Service\JobValidator\PropertyValidator\JobName;
use Chapi\Service\JobValidator\PropertyValidator\NotEmpty;
use Chapi\Service\JobValidator\PropertyValidator\Retries;
use Chapi\Service\JobValidator\PropertyValidator\Schedule;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidatorFactory implements ValidatorFactoryInterface
{
    /**
     * @var string[]
     */
    private static $aValidatorMap = [
        self::NOT_EMPTY_VALIDATOR => NotEmpty::DIC_NAME,
        self::NAME_VALIDATOR => JobName::DIC_NAME,
        self::EPSILON_VALIDATOR => Epsilon::DIC_NAME,
        self::BOOLEAN_VALIDATOR => IsBoolean::DIC_NAME,
        self::SCHEDULE_VALIDATOR => Schedule::DIC_NAME,
        self::ARRAY_VALIDATOR => IsArray::DIC_NAME,
        self::RETRY_VALIDATOR => Retries::DIC_NAME,
        self::CONSTRAINTS_VALIDATOR => Constraints::DIC_NAME,
        self::CONTAINER_VALIDATOR => Container::DIC_NAME,
        self::COMMAND_VALIDATOR => Command::DIC_NAME,
    ];

    /**
     * @var ContainerInterface
     */
    private $oServiceContainer;

    /**
     * ValidatorFactory constructor.
     * @param ContainerInterface $oServiceContainer
     */
    public function __construct(ContainerInterface $oServiceContainer)
    {
        $this->oServiceContainer = $oServiceContainer;
    }

    /**
     * @param int $iValidator
     * @return PropertyValidatorInterface
     */
    public function getValidator($iValidator)
    {
        if (!isset(self::$aValidatorMap[$iValidator]))
        {
            throw new \InvalidArgumentException(sprintf('Unknown validator type "%s"', $iValidator));
        }
        
        return $this->oServiceContainer->get(self::$aValidatorMap[$iValidator]);
    }
}