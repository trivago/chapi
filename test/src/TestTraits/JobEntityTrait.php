<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-13
 *
 */


namespace ChapiTest\src\TestTraits;

use Chapi\Entity\Chronos\JobEntity;
use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\Marathon\MarathonAppEntity;

trait JobEntityTrait
{
    private function getValidScheduledJobEntity($jobName = 'JobA')
    {
        $jobEntity = new ChronosJobEntity();

        $jobEntity->name = $jobName;
        $jobEntity->command = 'echo test';
        $jobEntity->description = 'description';
        $jobEntity->owner = 'mail@address.com';
        $jobEntity->ownerName = 'ownerName';
        $jobEntity->schedule = 'R/' . date('Y') . '-' . date('m') . '-01T02:00:00Z/PT30M';
        $jobEntity->scheduleTimeZone = 'Europe/Berlin';
        $jobEntity->epsilon = 'PT5M';

        return $jobEntity;
    }

    private function getValidDependencyJobEntity($jobName = 'JobA', $parent = 'JobB')
    {
        $jobEntity = new ChronosJobEntity();

        $jobEntity->name = $jobName;
        $jobEntity->command = 'echo test';
        $jobEntity->description = 'description';
        $jobEntity->owner = 'mail@address.com';
        $jobEntity->ownerName = 'ownerName';
        $jobEntity->parents = [$parent];
        $jobEntity->epsilon = 'PT5M';

        return $jobEntity;
    }

    private function getValidContainerJobEntity($jobName = 'JobA')
    {
        $jobEntity = $this->getValidScheduledJobEntity($jobName);

        $container = new JobEntity\ContainerEntity();
        $container->type = 'DOCKER';
        $container->image = 'libmesos/ubuntu';
        $container->network = 'BRIDGE';

        $volume = new JobEntity\ContainerVolumeEntity();
        $volume->containerPath = '/var/log/';
        $volume->hostPath = '/logs/';
        $volume->mode = 'RW';
        
        $container->volumes = [$volume];

        $jobEntity->container = $container;
        return $jobEntity;
    }

    private function createJobCollection()
    {
        $jobEntities = [
            $this->getValidScheduledJobEntity('JobA'),
            $this->getValidDependencyJobEntity('JobB', 'JobA'),
            $this->getValidScheduledJobEntity('JobC')
        ];

        return new JobCollection($jobEntities);
    }
}
