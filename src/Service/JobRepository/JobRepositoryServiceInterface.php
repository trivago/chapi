<?php
/**
 * @package: orchestra-
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 * @link:    http://
 */


namespace Chapi\Service\JobRepository;


interface JobRepositoryServiceInterface
{
    const DIC_NAME_CHRONOS = 'JobRepositoryChronos';

    /**
     * @param string $sJobName
     * @return \Chapi\Entity\Chronos\JobEntity
     */
    public function getJob($sJobName);

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs();
}