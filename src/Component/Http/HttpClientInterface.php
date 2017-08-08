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
     * @param string $url
     * @return HttpClientResponseInterface
     */
    public function get($url);

    /**
     * @param string $url
     * @param mixed $postData
     * @return HttpClientResponseInterface
     */
    public function postJsonData($url, $postData);

    /**
     * @param string $url
     * @return HttpClientResponseInterface
     */
    public function delete($url);

    /**
     * @param string $url
     * @param mixed $postData
     * @return HttpClientResponseInterface
     */
    public function putJsonData($url, $postData);
}
