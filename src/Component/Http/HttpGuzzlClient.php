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

    /**
     * @param string $sUrl
     * @param mixed $mPostData
     * @return HttpGuzzlResponse
     */
    public function postJsonData($sUrl, $mPostData)
    {
        $_oRequest = $this->oGuzzelClient->createRequest(
            'post',
            $sUrl,
            array('json' => $mPostData)
        );

        $_oResponse = $this->oGuzzelClient->send($_oRequest);

        return new HttpGuzzlResponse($_oResponse);
    }

    /**
     * @param string $sUrl
     * @return HttpGuzzlResponse
     */
    public function delete($sUrl)
    {
        $_oResponse = $this->oGuzzelClient->delete($sUrl);
        return new HttpGuzzlResponse($_oResponse);
    }
}