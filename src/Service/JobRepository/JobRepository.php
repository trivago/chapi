<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-28
 */


namespace Chapi\Service\JobRepository;


use Chapi\Entity\Chronos\JobCollection;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

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
     * @var JobFilterInterface
     */
    private $oJobFilter;

    /**
     * @param BridgeInterface $oRepositoryBridge
     */
    public function __construct(
        BridgeInterface $oRepositoryBridge,
        JobFilterInterface $oJobFilter
    )
    {
        $this->oRepositoryBridge = $oRepositoryBridge;
        $this->oJobFilter = $oJobFilter;
    }

    /**
     * @param string $sJobName
     * @return ChronosJobEntity
     */
    public function getJob($sJobName)
    {
        $_aJobs = $this->getJobs();
        if (isset($_aJobs[$sJobName]))
        {
            return $_aJobs[$sJobName];
        }

        // return new ChronosJobEntity();
        return null;
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    public function hasJob($sJobName)
    {
        $_aJobs = $this->getJobs();
        return (isset($_aJobs[$sJobName]));
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

        // apply filter
        $aJobs = array_filter(
            $this->oRepositoryBridge->getJobs(),
            array($this->oJobFilter, "isInteresting")
        );

        return $this->oJobCollection = new JobCollection(
            $aJobs
        );
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        if ($this->oRepositoryBridge->addJob($oJobEntity))
        {
            if (!is_null($this->oJobCollection)) // if no collection inited the new job will init by chronos request
            {
                $this->oJobCollection->offsetSet($oJobEntity->getKey(), $oJobEntity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param ChronosJobEntity|JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
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
        if (empty ($_oJobEntity->getKey()))
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