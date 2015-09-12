<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

use Chapi\Exception\HttpConnectionException;
use GuzzleHttp\ClientInterface;

class HttpGuzzlClient implements HttpClientInterface
{
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 30;

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
     * @throws HttpConnectionException
     */
    public function get($sUrl)
    {
        try
        {
            $_oResponse = $this->oGuzzelClient->get(
                $sUrl,
                [
                    'connect_timeout' => self::DEFAULT_CONNECTION_TIMEOUT,
                    'timeout' => self::DEFAULT_TIMEOUT
                ]
            );

            return new HttpGuzzlResponse($_oResponse);
        }
        catch (\Exception $oException)
        {
            throw new HttpConnectionException(
                sprintf('Can\'t get response from "%s"', $this->oGuzzelClient->getBaseUrl() . $sUrl),
                0,
                $oException
            );
        }
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
            ['json' => $mPostData]
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