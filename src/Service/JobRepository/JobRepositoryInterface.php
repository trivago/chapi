<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-29
 *
 */


namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

interface JobRepositoryInterface
{
    const DIC_NAME_CHRONOS = 'JobRepositoryChronos';
    const DIC_NAME_FILESYSTEM = 'JobRepositoryFileSystem';
    const DIC_NAME_MARATHON = 'JobRepositoryMarathon';

    /**
     * @param string $sJobName
     * @return ChronosJobEntity
     */
    public function getJob($sJobName);

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs();

    /**
     * @param string $sJobName
     * @return bool
     */
    public function hasJob($sJobName);

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity);

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity);

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName);
}