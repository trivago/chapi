<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 *
 */


namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Command extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'CommandValidator';
    const MESSAGE_TEMPLATE = '"%s" is not allowed to be empty for non docker jobs';

    /**
     * @inheritDoc
     */
    public function isValid($property, JobEntityInterface $jobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isCommandPropertyValid($jobEntity),
            $property,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    private function isCommandPropertyValid(JobEntityInterface $jobEntity)
    {
        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Required ChronosJobEntity. Something else found.');
        }

        if (is_null($jobEntity->container)) {
            return (!empty($jobEntity->command) && is_string($jobEntity->command));
        }
        
        return (empty($jobEntity->command) || is_string($jobEntity->command));
    }
}
