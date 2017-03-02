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
    public function isValid($sProperty, JobEntityInterface $oJobEntity)
    {
        return $this->returnIsValidHelper(
            $this->isContainerPropertyValid($oJobEntity->{$sProperty}),
            $sProperty,
            self::MESSAGE_TEMPLATE
        );
    }

    /**
     * @param JobEntity\ContainerEntity $oContainer
     * @return bool
     *
     * @see http://mesos.github.io/chronos/docs/api.html#adding-a-docker-job
     * This contains the subfields for the Docker container:
     *  type (required), image (required), forcePullImage (optional), network (optional),
     *  and volumes (optional)
     */
    private function isContainerPropertyValid($oContainer)
    {
        if (is_null($oContainer))
        {
            return true;
        }

        if (empty($oContainer->type) || empty($oContainer->image))
        {
            return false;
        }

        if (!is_array($oContainer->volumes))
        {
            return false;
        }

        foreach ($oContainer->volumes as $_oVolume)
        {
            if (!in_array($_oVolume->mode, ['RO', 'RW']))
            {
                return false;
            }
        }

        return true;
    }
}