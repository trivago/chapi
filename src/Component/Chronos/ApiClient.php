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
use Chapi\Exception\ApiClientException;

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
     * @inheritdoc
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
     * @throws ApiClientException
     */
    public function addingJob(JobEntity $oJobEntity)
    {
        $_sTargetUrl = '';

        if (!empty($oJobEntity->schedule) && empty($oJobEntity->parents))
        {
            $_sTargetUrl = '/scheduler/iso8601';
        } elseif (empty($oJobEntity->schedule) && !empty($oJobEntity->parents))
        {
            $_sTargetUrl = '/scheduler/dependency';
        }

        if (empty($_sTargetUrl))
        {
            throw new ApiClientException('No scheduler or dependency found. Can\'t get right target url.');
        }

        $_oResponse = $this->oHttpClient->postJsonData($_sTargetUrl, $oJobEntity);
        return ($_oResponse->getStatusCode() == 204);
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

    /**
     * @inheritdoc
     */
    public function getJobStats($sJobName)
    {
        $_oResponse = $this->oHttpClient->get('/scheduler/job/stat/' . $sJobName);
        if (200 == $_oResponse->getStatusCode())
        {
            return $_oResponse->json();
        }

        return [];
    }
}