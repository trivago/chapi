<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Service\Chronos;

use Chapi\Component\Chronos\ApiClientInterface;

class JobService implements JobServiceInterface
{
    /**
     * @var ApiClientInterface
     */
    private $oApiClient;

    /**
     * @param ApiClientInterface $oApiClient
     */
    public function __construct(
        ApiClientInterface $oApiClient
    )
    {
        $this->oApiClient = $oApiClient;
    }


    public function addJob()
    {
        //TODO: implement method
    }

    public function getJob($sJobName)
    {
        //TODO: implement method
    }

    public function getJobs()
    {
        return $this->oApiClient->listingJobs();
    }
}