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

class HttpGuzzleClient implements HttpClientInterface
{
    const DEFAULT_CONNECTION_TIMEOUT = 5;
    const DEFAULT_TIMEOUT = 30;

    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @var AuthEntity
     */
    private $authEntity;

    /**
     * @param ClientInterface $guzzleClient
     * @param AuthEntity $authEntity
     */
    public function __construct(
        ClientInterface $guzzleClient,
        AuthEntity $authEntity
    ) {
        $this->guzzleClient = $guzzleClient;
        $this->authEntity = $authEntity;
    }

    /**
     * @param string $url
     * @return HttpClientResponseInterface
     * @throws HttpConnectionException
     */
    public function get($url)
    {
        $requestOptions = $this->getDefaultRequestOptions();

        try {
            $response = $this->guzzleClient->request('GET', $url, $requestOptions);
            return new HttpGuzzleResponse($response);
        } catch (ClientException $exception) { // 400 level errors
            throw new HttpConnectionException(
                sprintf('Client error: Calling %s returned %d', $this->guzzleClient->getConfig('base_uri') . $url, $exception->getCode()),
                $exception->getCode(),
                $exception
            );
        } catch (ServerException $exception) { // 500 level errors
            throw new HttpConnectionException(
                sprintf('Server error: Calling %s returned %d', $this->guzzleClient->getConfig('base_uri') . $url, $exception->getCode()),
                $exception->getCode(),
                $exception
            );
        } catch (TooManyRedirectsException $exception) { // too many redirects to follow
            throw new HttpConnectionException(
                sprintf('Request to %s failed due to too many redirects', $this->guzzleClient->getConfig('base_uri') . $url),
                HttpConnectionException::ERROR_CODE_TOO_MANY_REDIRECT_EXCEPTION,
                $exception
            );
        } catch (ConnectException $exception) { // networking error
            throw new HttpConnectionException(
                sprintf('Cannot connect to %s due to some networking error', $this->guzzleClient->getConfig('base_uri') . $url),
                HttpConnectionException::ERROR_CODE_CONNECT_EXCEPTION,
                $exception
            );
        } catch (RequestException $exception) { // networking error (connection timeout, DNS errors, etc.)
            throw new HttpConnectionException(
                sprintf('Cannot connect to %s due to networking error', $this->guzzleClient->getConfig('base_uri') . $url),
                HttpConnectionException::ERROR_CODE_REQUEST_EXCEPTION,
                $exception
            );
        } catch (\Exception $exception) {
            throw new HttpConnectionException(
                sprintf('Can\'t get response from "%s"', $this->guzzleClient->getConfig('base_uri') . $url),
                HttpConnectionException::ERROR_CODE_UNKNOWN,
                $exception
            );
        }
    }

    /**
     * @param string $url
     * @param mixed $postData
     * @return HttpGuzzleResponse
     */
    public function postJsonData($url, $postData)
    {
        return $this->sendJsonDataWithMethod('POST', $url, $postData);
    }

    /**
     * @param string $url
     * @param mixed $mPutData
     * @return HttpGuzzleResponse
     */
    public function putJsonData($url, $mPutData)
    {
        return $this->sendJsonDataWithMethod('PUT', $url, $mPutData);
    }


    /**
     * @param string $url
     * @return HttpGuzzleResponse
     */
    public function delete($url)
    {
        $requestOptions = $this->getDefaultRequestOptions();
        $response = $this->guzzleClient->request('DELETE', $url, $requestOptions);
        return new HttpGuzzleResponse($response);
    }

    /**
     * @param string $method
     * @param string $url
     * @param mixed $data
     * @return HttpGuzzleResponse
     */
    private function sendJsonDataWithMethod($method, $url, $data)
    {
        $requestOptions = $this->getDefaultRequestOptions();
        $requestOptions['json'] = $data;

        $response = $this->guzzleClient->request($method, $url, $requestOptions);

        return new HttpGuzzleResponse($response);
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
        $requestOptions = [
            'connect_timeout' => self::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => self::DEFAULT_TIMEOUT
        ];

        if (!empty($this->authEntity->username)
            && !empty($this->authEntity->password)
        ) {
            $requestOptions['auth'] = [
                $this->authEntity->username,
                $this->authEntity->password
            ];
        }

        return $requestOptions;
    }
}
