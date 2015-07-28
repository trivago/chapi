<?php
/**
 * @package: orchestra-
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 * @link:    http://
 */


namespace Chapi\Component\Chronos;


interface ApiClientInterface
{
    const DIC_NAME = 'ApiClientInterface';

    /**
     * @link: https://mesos.github.io/chronos/docs/api.html#listing-jobs
     * @return []
     */
    public function listingJobs();
}