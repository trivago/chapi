<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 */


namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\JobEntity;

class JobRepository implements JobRepositoryInterface
{

    /**
     * @var JobCollection
     */
    private $oJobCollection;

    /**
     * @var BridgeInterface
     */
    private $oRepositoryBridge;

    /**
     * @param BridgeInterface $oRepositoryBridge
     */
    function __construct(
        BridgeInterface $oRepositoryBridge
    )
    {
        $this->oRepositoryBridge = $oRepositoryBridge;
    }

    /**
     * @param string $sJobName
     * @return \Chapi\Entity\Chronos\JobEntity
     */
    public function getJob($sJobName)
    {
        $_aJobs = $this->getJobs();
        if (isset($_aJobs[$sJobName]))
        {
            return $_aJobs[$sJobName];
        }

        return new JobEntity();
    }

    /**
     * @return \Chapi\Entity\Chronos\JobCollection
     */
    public function getJobs()
    {
        if (!is_null($this->oJobCollection))
        {
            return $this->oJobCollection;
        }

        return $this->oJobCollection = new JobCollection(
            $this->oRepositoryBridge->getJobs()
        );
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addJob(JobEntity $oJobEntity)
    {
        return $this->oRepositoryBridge->addJob($oJobEntity);
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntity $oJobEntity)
    {
        return $this->oRepositoryBridge->updateJob($oJobEntity);
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName)
    {
        $_oJobEntity = $this->getJob($sJobName);
        if (empty ($_oJobEntity->name))
        {
            throw new \InvalidArgumentException(sprintf('Can\'t remove unknown job "%s"', $sJobName));
        }

        if ($this->oRepositoryBridge->removeJob($_oJobEntity))
        {
            $this->oJobCollection->offsetUnset($_oJobEntity->name);
            return true;
        }

        return false;
    }
}