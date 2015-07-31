<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */


namespace Chapi\Component\Chronos;


use Chapi\Component\Http\HttpClientInterface;
use Chapi\Entity\Chronos\JobEntity;

class ApiClient implements ApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $oHttpClient;

    /**
     * @param HttpClientInterface $oHttpClient
     */
    public function __construct(
        HttpClientInterface $oHttpClient
    )
    {
        $this->oHttpClient = $oHttpClient;
    }

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     */
    public function listingJobs()
    {
        $_oResponse = $this->oHttpClient->get('/scheduler/jobs');
        if (200 == $_oResponse->getStatusCode())
        {
            return $_oResponse->json();
        }

        return [];
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function addingJob(JobEntity $oJobEntity)
    {
        if (!empty($oJobEntity->schedule) && empty($oJobEntity->parents))
        {
            $_oResponse = $this->oHttpClient->postJsonData('/scheduler/iso8601', $oJobEntity);
            return ($_oResponse->getStatusCode() == 204);
        }

        return false;
    }

    /**
     * @param JobEntity $oJobEntity
     * @return bool
     */
    public function updatingJob(JobEntity $oJobEntity)
    {
        return $this->addingJob($oJobEntity);
    }

    /**
     * @param string $sJobName
     * @return bool
     */
    public function removeJob($sJobName)
    {
        $_oResponse = $this->oHttpClient->delete('/scheduler/job/' . $sJobName);
        return ($_oResponse->getStatusCode() == 204);
    }
}