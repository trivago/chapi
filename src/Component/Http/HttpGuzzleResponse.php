<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

use Psr\Http\Message\ResponseInterface;

class HttpGuzzleResponse implements HttpClientResponseInterface
{

    /**
     * @var ResponseInterface
     */
    private $guzzleResponse;

    /**
     * @param ResponseInterface $guzzleResponse
     */
    public function __construct(
        ResponseInterface $guzzleResponse
    ) {
        $this->guzzleResponse = $guzzleResponse;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->guzzleResponse->getStatusCode();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return (string) $this->guzzleResponse->getBody();
    }

    /**
     * @return array
     */
    public function json()
    {
        return json_decode($this->getBody(), true);
    }
}
