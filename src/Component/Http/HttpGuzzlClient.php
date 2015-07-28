<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

use GuzzleHttp\ClientInterface;

class HttpGuzzlClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $oGuzzelClient;

    /**
     * @param ClientInterface $oGuzzelClient
     */
    public function __construct(
        ClientInterface $oGuzzelClient
    )
    {
        $this->oGuzzelClient = $oGuzzelClient;

    }

    /**
     * @param string $sUrl
     * @return HttpClientResponseInterface
     */
    public function get($sUrl)
    {
        $_oResponse = $this->oGuzzelClient->get($sUrl);
        return new HttpGuzzlResponse($_oResponse);
    }
}