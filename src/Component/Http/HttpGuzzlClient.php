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
use Chapi\Entity\Http\AuthEntity;
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
     * @var AuthEntity
     */
    private $oAuthEntity;

    /**
     * @param ClientInterface $oGuzzelClient
     * @param AuthEntity $oAuthEntity
     */
    public function __construct(
        ClientInterface $oGuzzelClient,
        AuthEntity $oAuthEntity
    )
    {
        $this->oGuzzelClient = $oGuzzelClient;
        $this->oAuthEntity = $oAuthEntity;
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
        if (!empty($this->oAuthEntity->username)
            && !empty($this->oAuthEntity->password)
        )
        {
            $aOptions['auth'] = [
                $this->oAuthEntity->username,
                $this->oAuthEntity->password
            ];
        }

        return $aOptions;
    }
}