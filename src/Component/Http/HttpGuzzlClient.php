<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-07-28
 *
 */

namespace Chapi\Component\Http;

use Chapi\Entity\Http\AuthEntity;
use Chapi\Exception\HttpConnectionException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;

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
        $_aRequestOptions = $this->getDefaultRequestOptions();

        try
        {
            $_oResponse = $this->oGuzzelClient->request('GET', $sUrl, $_aRequestOptions);
            return new HttpGuzzlResponse($_oResponse);
        }
        catch (ClientException $oException) // 400 level errors
        {
            throw new HttpConnectionException(
                sprintf('Client error: Calling %s returned %d', $this->oGuzzelClient->getConfig('base_uri') . $sUrl, $oException->getCode()),
                $oException->getCode(),
                $oException
            );
        }
        catch (ServerException $oException) // 500 level errors
        {
            throw new HttpConnectionException(
                sprintf('Server error: Calling %s returned %d', $this->oGuzzelClient->getConfig('base_uri') . $sUrl, $oException->getCode()),
                $oException->getCode(),
                $oException
            );
        }
        catch (TooManyRedirectsException $oException) // too many redirects to follow
        {
            throw new HttpConnectionException(
                sprintf('Request to %s failed due to too many redirects', $this->oGuzzelClient->getConfig('base_uri') . $sUrl),
                HttpConnectionException::ERROR_CODE_TOO_MANY_REDIRECT_EXCEPTION,
                $oException
            );
        }
        catch (ConnectException $oException) // networking error
        {
            throw new HttpConnectionException(
                sprintf('Cannot connect to %s due to some networking error', $this->oGuzzelClient->getConfig('base_uri') . $sUrl),
                HttpConnectionException::ERROR_CODE_CONNECT_EXCEPTION,
                $oException
            );
        }
        catch (RequestException $oException) // networking error (connection timeout, DNS errors, etc.)
        {
            throw new HttpConnectionException(
                sprintf('Cannot connect to %s due to networking error', $this->oGuzzelClient->getConfig('base_uri') . $sUrl),
                HttpConnectionException::ERROR_CODE_REQUEST_EXCEPTION,
                $oException
            );
        }
        catch (\Exception $oException)
        {
            throw new HttpConnectionException(
                sprintf('Can\'t get response from "%s"', $this->oGuzzelClient->getConfig('base_uri') . $sUrl),
                HttpConnectionException::ERROR_CODE_UNKNOWN,
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
        return $this->sendJsonDataWithMethod('POST', $sUrl, $mPostData);
    }

    /**
     * @param string $sUrl
     * @param mixed $mPutData
     * @return HttpGuzzlResponse
     */
    public function putJsonData($sUrl, $mPutData)
    {
        return $this->sendJsonDataWithMethod('PUT', $sUrl, $mPutData);
    }


    /**
     * @param string $sUrl
     * @return HttpGuzzlResponse
     */
    public function delete($sUrl)
    {
        $_aRequestOptions = $this->getDefaultRequestOptions();
        $_oResponse = $this->oGuzzelClient->request('DELETE', $sUrl, $_aRequestOptions);
        return new HttpGuzzlResponse($_oResponse);
    }

    /**
     * @param $sMethod
     * @param $sUrl
     * @param $mData
     * @return HttpGuzzlResponse
     */
    private function sendJsonDataWithMethod($sMethod, $sUrl, $mData)
    {
        $_aRequestOptions = $this->getDefaultRequestOptions();
        $_aRequestOptions['json'] = $mData;

        $_oResponse = $this->oGuzzelClient->request($sMethod, $sUrl, $_aRequestOptions);

        return new HttpGuzzlResponse($_oResponse);
    }

    /**
     * Returns default options for the HTTP request.
     * If an username and password is provided, auth
     * header will be applied as well.
     *
     * @return array<string,integer|string>
     */
    private function getDefaultRequestOptions()
    {
        $_aRequestOptions = [
            'connect_timeout' => self::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => self::DEFAULT_TIMEOUT
        ];

        if (!empty($this->oAuthEntity->username)
            && !empty($this->oAuthEntity->password)
        )
        {
            $_aRequestOptions['auth'] = [
                $this->oAuthEntity->username,
                $this->oAuthEntity->password
            ];
        }

        return $_aRequestOptions;
    }

}