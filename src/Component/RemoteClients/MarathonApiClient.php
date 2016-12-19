<?php

namespace Chapi\Component\RemoteClients;


use Chapi\Component\Http\HttpClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;

class MarathonApiClient implements ApiClientInterface
{

    /** @var HttpClientInterface  */
    private $oHttpClient;

    public function __construct(
        HttpClientInterface $oHttpClient
    )
    {
        $this->oHttpClient = $oHttpClient;
    }

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return array
     */
    public function listingJobs()
    {
        $oResponse = $this->oHttpClient->get("/v2/apps");
        if (200 == $oResponse->getStatusCode())
        {
            return $oResponse->json(false);
        }

        return [];
    }

    /**
     * @param JobEntityInterface $oJobEntity
     * @return bool
     */
    public function addingJob(JobEntityInterface $oJobEntity)
    {
        $_sTargetEndpoint = "/v2/apps";

        $_oResponse = $this->oHttpClient->postJsonData($_sTargetEndpoint, $oJobEntity);
        return ($_oResponse->getStatusCode() == 201);
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $oJobEntity
     * @return bool
     */
    public function updatingJob(JobEntityInterface $oJobEntity)
    {
        $_sTargetEndpoint = "/v2/apps";

        $_oResponse = $this->oHttpClient->putJsonData($_sTargetEndpoint, $oJobEntity);
        return ($_oResponse->getstatusCode() == 200);
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName)
    {
        $_sTargetEndpoint = "/v2/apps/".$sJobName;

        $_oResponse = $this->oHttpClient->delete(sUrl);
        return ($_oResponse->getStatusCode() == 200);
    }

    /**
     * @param string $sJobName
     * @return array
     */
    public function getJobStats($sJobName)
    {
        return [];
    }
}