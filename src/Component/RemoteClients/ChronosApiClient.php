<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */


namespace Chapi\Component\RemoteClients;

use Chapi\Component\RemoteClients\ApiClientInterface;
use Chapi\Component\Http\HttpClientInterface;
use Chapi\Entity\Chronos\ChronosJobEntity;
use Chapi\Entity\JobEntityInterface;
use Chapi\Exception\ApiClientException;
use Chapi\Exception\HttpConnectionException;
use Symfony\Component\Config\Definition\Exception\Exception;

class ChronosApiClient implements ApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * @inheritdoc
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     */
    public function listingJobs()
    {
        return $this->sendGetJsonRequest('/scheduler/jobs');
    }

    /**
     * @param JobEntityInterface $jobEntity
     * @return bool
     * @throws ApiClientException
     */
    public function addingJob(JobEntityInterface $jobEntity)
    {
        $targetUrl = '';

        if (!$jobEntity instanceof ChronosJobEntity) {
            throw new \RuntimeException('Expected ChronosJobEntity.');
        }

        if (!empty($jobEntity->schedule) && empty($jobEntity->parents)) {
            $targetUrl = '/scheduler/iso8601';
        } elseif (empty($jobEntity->schedule) && !empty($jobEntity->parents)) {
            $targetUrl = '/scheduler/dependency';
        }

        if (empty($targetUrl)) {
            throw new ApiClientException('No scheduler or dependency found. Can\'t get right target url.');
        }

        $response = $this->httpClient->postJsonData($targetUrl, $jobEntity);
        return ($response->getStatusCode() == 204);
    }

    /**
     * @param JobEntityInterface|ChronosJobEntity $jobEntity
     * @return bool
     * @throws ApiClientException
     */
    public function updatingJob(JobEntityInterface $jobEntity)
    {
        return $this->addingJob($jobEntity);
    }

    /**
     * @param string $jobName
     * @return bool
     */
    public function removeJob($jobName)
    {
        $response = $this->httpClient->delete('/scheduler/job/' . $jobName);
        return ($response->getStatusCode() == 204);
    }

    /**
     * @inheritdoc
     */
    public function getJobStats($jobName)
    {
        return $this->sendGetJsonRequest('/scheduler/job/stat/' . $jobName);
    }

    /**
     * @param string $url
     * @return array
     */
    private function sendGetJsonRequest($url)
    {
        $response = $this->httpClient->get($url);
        if (200 == $response->getStatusCode()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Returns true if the client can be connected to.
     * @return bool
     */
    public function ping()
    {
        try {
            $this->httpClient->get('/scheduler/jobs');
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
