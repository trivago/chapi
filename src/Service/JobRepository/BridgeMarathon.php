<?php
/**
 * @package: chapi
 *
 * @author:  bthapaliya
 * @since:   2016-12-02
 */

namespace Chapi\Service\JobRepository;


use Chapi\Component\Cache\CacheInterface;
use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Psr\Log\LoggerInterface;

class BridgeMarathon implements BridgeInterface
{
    /**
     * @var \Chapi\Component\RemoteClients\ApiClientInterface
     */
    private $oApiClient;
    /**
     * @var JobEntityValidatorServiceInterface
     */
    private $oJobEntityValidatorService;
    /**
     * @var CacheInterface
     */
    private $oCache;
    /**
     * @var LoggerInterface
     */
    private $oLogger;

    public function __construct(
        ApiClientInterface $oApiClient,
        CacheInterface $oCache,
        JobEntityValidatorServiceInterface $oJobEntityValidatorService,
        LoggerInterface $oLogger
    )
    {

        $this->oApiClient = $oApiClient;
        $this->oJobEntityValidatorService = $oJobEntityValidatorService;
        $this->oCache = $oCache;
        $this->oLogger = $oLogger;
    }

    /**
     * @return JobEntityInterface[]
     */
    public function getJobs()
    {
        $_aApps = [];
        return $_aApps;
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement addJob() method.
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function updateJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement updateJob() method.
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function removeJob(JobEntityInterface $oJobEntity)
    {
        // TODO: Implement removeJob() method.
    }


}