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
        $_oJobEntity->schedule = 'R/2015-' . date('m') . '-01T02:00:00Z/P1M';
        $_oJobEntity->scheduleTimeZone = 'Europe/Berlin';

        return $_oJobEntity;
    }

    private function getValidDependencyJobEntity($sJobName = 'JobA')
    {
        $_oJobEntity = new JobEntity();

        $_oJobEntity->name = $sJobName;
        $_oJobEntity->command = 'echo test';
        $_oJobEntity->description = 'description';
        $_oJobEntity->owner = 'mail@address.com';
        $_oJobEntity->ownerName = 'ownerName';
        $_oJobEntity->parents = ['JobB'];

        return $_oJobEntity;
    }
}