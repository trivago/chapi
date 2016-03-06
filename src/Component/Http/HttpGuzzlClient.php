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
     * @var array
     */
    private $aGuzzelClientConfig;

    /**
     * @param ClientInterface $oGuzzelClient
     * @param array $aGuzzelClientConfig
     */
    public function __construct(
        ClientInterface $oGuzzelClient,
        array $aGuzzelClientConfig = []
    )
    {
        $this->oGuzzelClient = $oGuzzelClient;
        $this->aGuzzelClientConfig = $aGuzzelClientConfig;
    }

    /**
     * @param string $sUrl
     * @return HttpClientResponseInterface
     * @throws HttpConnectionException
     */
    public function get($sUrl)
    {
        $_aRequestOptions = [
            'connect_timeout' => self::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => self::DEFAULT_TIMEOUT
        ];
        $_aRequestOptions = $this->addAuthOption($_aRequestOptions);

        try
        {
            $_oResponse = $this->oGuzzelClient->get($sUrl, $_aRequestOptions);
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
        $_aRequestOptions = ['json' => $mPostData];
        $_aRequestOptions = $this->addAuthOption($_aRequestOptions);

        $_oRequest = $this->oGuzzelClient->createRequest('post', $sUrl, $_aRequestOptions);
        $_oResponse = $this->oGuzzelClient->send($_oRequest);

        return new HttpGuzzlResponse($_oResponse);
    }

    /**
     * @param string $sUrl
     * @return HttpGuzzlResponse
     */
    public function delete($sUrl)
    {
        $_aRequestOptions = [];
        $_aRequestOptions = $this->addAuthOption($_aRequestOptions);

        $_oResponse = $this->oGuzzelClient->delete($sUrl, $_aRequestOptions);
        return new HttpGuzzlResponse($_oResponse);
    }

    /**
     * Adds authentication headers according the Guzzle http options.
     *
     * @param array $aOptions
     * @return array
     */
    private function addAuthOption(array $aOptions) {
        if (array_key_exists('username', $this->aGuzzelClientConfig)
            && $this->aGuzzelClientConfig['username']
            && array_key_exists('password', $this->aGuzzelClientConfig)
            && $this->aGuzzelClientConfig['password']
        )
        {
            $aOptions['auth'] = [
                $this->aGuzzelClientConfig['username'],
                $this->aGuzzelClientConfig['password']
            ];
        }

        return $aOptions;
    }
}