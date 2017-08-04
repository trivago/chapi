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

class HttpGuzzlResponse implements HttpClientResponseInterface
{

    /**
     * @var
     */
    private $oGuzzlResponse;

    /**
     * @param  $oGuzzlResponse
     */
    public function __construct(
        ResponseInterface $oGuzzlResponse
    ) {
        $this->oGuzzlResponse = $oGuzzlResponse;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->oGuzzlResponse->getStatusCode();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return (string) $this->oGuzzlResponse->getBody();
    }

    /**
     * @return array
     */
    public function json()
    {
        return json_decode($this->getBody(), true);
    }
}
