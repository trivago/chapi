<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-13
 *
 */


namespace ChapiTest\src\TestTraits;


use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\JobEntity;

trait JobEntityTrait
{
    private function getValidScheduledJobEntity($sJobName = 'JobA')
    {
        $_oJobEntity = new JobEntity();

        $_oJobEntity->name = $sJobName;
        $_oJobEntity->command = 'echo test';
        $_oJobEntity->description = 'description';
        $_oJobEntity->owner = 'mail@address.com';
        $_oJobEntity->ownerName = 'ownerName';
        $_oJobEntity->schedule = 'R/2015-' . date('m') . '-01T02:00:00Z/PT30M';
        $_oJobEntity->scheduleTimeZone = 'Europe/Berlin';
        $_oJobEntity->epsilon = 'PT5M';

        return $_oJobEntity;
    }

    private function getValidDependencyJobEntity($sJobName = 'JobA', $sParent = 'JobB')
    {
        $_oJobEntity = new JobEntity();

        $_oJobEntity->name = $sJobName;
        $_oJobEntity->command = 'echo test';
        $_oJobEntity->description = 'description';
        $_oJobEntity->owner = 'mail@address.com';
        $_oJobEntity->ownerName = 'ownerName';
        $_oJobEntity->parents = [$sParent];
        $_oJobEntity->epsilon = 'PT5M';

        return $_oJobEntity;
    }

    private function createJobCollection()
    {
        $_aJobEntities = [
            $this->getValidScheduledJobEntity('JobA'),
            $this->getValidDependencyJobEntity('JobB', 'JobA'),
            $this->getValidScheduledJobEntity('JobC')
        ];

        return new JobCollection($_aJobEntities);
    }
}