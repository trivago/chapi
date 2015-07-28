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
}