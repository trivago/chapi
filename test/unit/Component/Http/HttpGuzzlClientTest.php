<?php
/**
 * @package: chapi
 *
 * @author:  msiebeneicher
 * @since:   2015-08-07
 *
 */


namespace unit\Component\Http;

use Chapi\Component\Http\HttpGuzzleClient;
use Chapi\Entity\Http\AuthEntity;
use Prophecy\Argument;

class HttpGuzzlClientTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $guzzleClient;

    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $guzzleResponse;

    protected function setUp(): void
    {
        $this->guzzleClient = $this->prophesize('GuzzleHttp\ClientInterface');
        $this->guzzleResponse = $this->prophesize('Psr\Http\Message\ResponseInterface');
    }

    public function testGetSuccess()
    {
        $url = '/url/for/test';

        $authEntity = new AuthEntity("", "");
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT
        ];

        $this->guzzleClient->request(Argument::exact('GET'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal())
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $httpGuzzleClient->get($url));
    }

    public function testGetSuccessWithHttpBasicAuth()
    {
        $url = '/url/for/test';
        $auth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $authEntity = new AuthEntity($auth['username'], $auth['password']);
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT,
            'auth' => [$auth['username'], $auth['password']]
        ];

        $this->guzzleClient->request(Argument::exact('GET'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal())
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $response = $httpGuzzleClient->get($url);
        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $response);
    }

    public function testGetFailure()
    {
        $this->expectException(\Chapi\Exception\HttpConnectionException::class);

        $url = '/url/for/test';

        $authEntity = new AuthEntity("", "");
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT
        ];

        $this->guzzleClient->request(Argument::exact('GET'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willThrow(new \Exception('test exception'))
        ;

        $this->guzzleClient->getConfig(Argument::exact('base_uri'))
            ->shouldBeCalledTimes(1)
            ->willReturn('http://www.abc.com')
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertNull($httpGuzzleClient->get($url));
    }

    public function testPostJsonDataSuccess()
    {
        $url = '/url/for/test';
        $postData = ['data' => [1, 2, 3]];
        $_aGuzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT,
            'json' => $postData
        ];
        $authEntity = new AuthEntity("", "");
        $requestInterface = $this->prophesize('GuzzleHttp\Message\RequestInterface');

        $this->guzzleClient->request(Argument::exact('POST'), Argument::exact($url), Argument::exact($_aGuzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal())
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $httpGuzzleClient->postJsonData($url, $postData));
    }

    public function testPostJsonDataSuccessWithHttpBasicAuth()
    {
        $url = '/url/for/test';
        $auth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $authEntity = new AuthEntity($auth['username'], $auth['password']);
        $postData = ['data' => [1, 2, 3]];
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT,
            'json' => $postData,
            'auth' => [$auth['username'], $auth['password']]
        ];

        $requestInterface = $this->prophesize('GuzzleHttp\Message\RequestInterface');

        $this->guzzleClient->request(Argument::exact('POST'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal());

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $httpGuzzleClient->postJsonData($url, $postData));
    }

    public function testDeleteSuccess()
    {
        $url = '/url/for/test';
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT,
        ];
        $authEntity = new AuthEntity("", "");
        $this->guzzleClient->request(Argument::exact('DELETE'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal())
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $httpGuzzleClient->delete($url));
    }

    public function testDeleteSuccessWithHttpBasicAuth()
    {
        $url = '/url/for/test';
        $auth = [
            'username' => 'user',
            'password' => 'pass'
        ];
        $authEntity = new AuthEntity($auth['username'], $auth['password']);
        $guzzleOptions = [
            'connect_timeout' => HttpGuzzleClient::DEFAULT_CONNECTION_TIMEOUT,
            'timeout' => HttpGuzzleClient::DEFAULT_TIMEOUT,
            'auth' => [$auth['username'], $auth['password']]
        ];

        $this->guzzleClient->request(Argument::exact('DELETE'), Argument::exact($url), Argument::exact($guzzleOptions))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->guzzleResponse->reveal())
        ;

        $httpGuzzleClient = new HttpGuzzleClient($this->guzzleClient->reveal(), $authEntity);

        $this->assertInstanceOf('Chapi\Component\Http\HttpClientResponseInterface', $httpGuzzleClient->delete($url));
    }
}
