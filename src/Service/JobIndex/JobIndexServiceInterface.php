<?php
/**
 * @package: Chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-31
 *
 */


namespace Chapi\Service\JobIndex;


interface JobIndexServiceInterface
{
    const DIC_NAME = 'JobIndexServiceInterface';

    public function addJob($sJobName);

    public function addJobs(array $aJobNames);

    public function removeJob($sJobName);

    public function removeJobs(array $aJobNames);

    public function resetJobIndex();

    public function getJobIndex();
}