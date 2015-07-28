<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

interface HttpClientInterface
{
    const DIC_NAME_GUZZLE = 'HttpClientGuzzle';

    /**
     * @param string $sUrl
     * @return HttpClientResponseInterface
     */
    public function get($sUrl);
}