<?php

namespace Chapi\Component\RemoteClients;

use Chapi\Component\Http\HttpClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Exception\HttpConnectionException;
use Symfony\Component\Config\Definition\Exception\Exception;

class MarathonApiClient implements ApiClientInterface
{

    /** @var HttpClientInterface  */
    private $httpClient;

    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return array
     */
    public function listingJobs()
    {
        $response = $this->httpClient->get('/v2/apps');
        if (200 == $response->getStatusCode()) {
            return $response->json();
        }

        return [];
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     */
    public function addingJob(JobEntityInterface $jobEntity)
    {
        $targetEndpoint = '/v2/apps';

        $response = $this->httpClient->postJsonData($targetEndpoint, $jobEntity);
        return ($response->getStatusCode() == 201);
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $jobEntity
     * @return bool
     */
    public function updatingJob(JobEntityInterface $jobEntity)
    {
        $jobName = $jobEntity->getKey();
        $targetEndpoint = '/v2/apps/' . $jobName;

        $response = $this->httpClient->putJsonData($targetEndpoint, $jobEntity);
        return ($response->getStatusCode() == 200);
    }

    /**
     * @param string $jobName
     * @return bool
     */
    public function removeJob($jobName)
    {
        $targetEndpoint = '/v2/apps/' . $jobName;

        $response = $this->httpClient->delete($targetEndpoint);
        return ($response->getStatusCode() == 200);
    }

    /**
     * @param string $jobName
     * @return array
     */
    public function getJobStats($jobName)
    {
        return [];
    }

    /**
     * Returns true if the client can be connected to.
     * @return bool
     */
    public function ping()
    {
        try {
            $this->httpClient->get('/v2/info');
        } catch (HttpConnectionException $exception) {
            if ($exception->getCode() == HttpConnectionException::ERROR_CODE_REQUEST_EXCEPTION ||
                $exception->getCode() == HttpConnectionException::ERROR_CODE_CONNECT_EXCEPTION
            ) {
                return false;
            }
        }
        return true;
    }
}
