<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2016-11-10
 */


namespace Chapi\Service\JobValidator\PropertyValidator;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Service\JobValidator\PropertyValidatorInterface;

class Container extends AbstractPropertyValidator implements PropertyValidatorInterface
{
    const DIC_NAME = 'ContainerValidator';
    const MESSAGE_TEMPLATE = '"%s" is not valid. Type (required), image (required), forcePullImage (optional), network (optional), and volumes (optional)';

    /**
     * @inheritDoc
     */
    public function isValid($property, JobEntityInterface $jobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isContainerPropertyValid($jobEntity->{$property}),
            $property,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntity\ContainerEntity $container
     * @return bool
     *
     * @see http://mesos.github.io/chronos/docs/api.html#adding-a-docker-job
     * This contains the subfields for the Docker container:
     *  type (required), image (required), forcePullImage (optional), network (optional),
     *  and volumes (optional)
     */
    private function isContainerPropertyValid($container)
    {
        if (is_null($container)) {
            return true;
        }

        if (empty($container->type) || empty($container->image)) {
            return false;
        }

        if (!is_array($container->volumes)) {
            return false;
        }

        foreach ($container->volumes as $volume) {
            if (!in_array($volume->mode, ['RO', 'RW'])) {
                return false;
            }
        }

        return true;
    }
}
